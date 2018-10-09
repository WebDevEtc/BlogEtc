<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Swis\LaravelFulltext\Search;
use WebDevEtc\BlogEtc\Captcha\CaptchaAbstract;
use WebDevEtc\BlogEtc\Captcha\UsesCaptcha;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\FeedRequest;

/**
 * Class BlogEtcReaderController
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcReaderController extends Controller
{

    use UsesCaptcha;

    /**
     * Show blog posts
     * If category_slug is set, then only show from that category
     *
     * @param null $category_slug
     * @return mixed
     */
    public function index( $category_slug = null)
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged in user can manageb log posts
        $posts = BlogEtcPost::orderBy("posted_at", "desc");
        $title = 'Viewing blog'; // default title...

        if ($category_slug) {
            $category = BlogEtcCategory::where("slug", $category_slug)->firstOrFail();
            $posts = $posts->join("blog_etc_post_categories", "blog_etc_post_categories.blog_etc_post_id",
                'blog_etc_posts.id')
                ->where("blog_etc_post_categories.blog_etc_category_id", $category->id);
            \View::share('blogetc_category', $category); // so the view can say "You are viewing $CATEGORYNAME category posts"

            $title = 'Viewing posts in ' . $category->category_name . " category"; // hardcode title here...
        }

        $posts = $posts->select("blog_etc_posts.*")// because of the join, we don't want to select anything else
        ->paginate(config("blogetc.per_page", 10));

        return view("blogetc::index",[
            'posts' => $posts,
            'title' => $title,
        ]);
    }

    /**
     * Show the search results for $_GET['s']
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function search(Request $request)
    {

        if (!config("blogetc.search.search_enabled")) {
            throw new \Exception("Search is disabled");
        }

        $query = $request->get("s");

        $search = new Search();
        $search_results =$search->run($query);

        \View::share("title","Search results for " . e($query));

        return view("blogetc::search",['query'=>$query,'search_results'=>$search_results]);

    }

    /**
     * RSS Feed
     * This is a long (but quite simple) method to show an RSS feed
     * It makes use of Laravelium\Feed\Feed.
     *
     * @param FeedRequest $request
     * @return mixed
     */
    public function feed(FeedRequest $request)
    {
        /** @var  \Laravelium\Feed\Feed $feed */
        $feed = \App::make("feed");





        // if a logged in user views the RSS feed it will get cached, and if they are an admin user then it'll show all posts (even if it is not set as published)
        $user = \Auth::check() ? \Auth::user()->id : 'guest';

        $feed->setCache(
            config("blogetc.rssfeed.cache_in_minutes", 60),
            "blog-" . $request->getFeedType() . $user
        );

        if (!$feed->isCached()) {

            // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged in user can manageb log posts
            $posts = BlogEtcPost::orderBy("posted_at", "desc")
                ->limit( config("blogetc.rssfeed.posts_to_show_in_rss_feed", 10))
                ->get();


            $feed->title = config("app.name") . ' Blog';
            $feed->description = config("blogetc.rssfeed.description", "Our blog RSS feed");
            $feed->link = route('blogetc.index');
            $feed->setDateFormat('carbon');

            $feed->pubdate = isset($posts[0]) ? $posts[0]->posted_at : Carbon::now()->subYear(10);

            $feed->lang = config("blogetc.rssfeed.language", "en");
            $feed->setShortening(config("blogetc.rssfeed.should_shorten_text", true)); // true or false
            $feed->setTextLimit(config("blogetc.rssfeed.text_limit", 100)); // maximum length of description text


            /** @var BlogEtcPost $post */
            foreach ($posts as $post) {
                $feed->add($post->title,
                    $post->author_name,
                    $post->url(),
                    $post->posted_at,
                    $post->short_description,
                    $post->generate_introduction()
                );
            }

        }

        return $feed->render($request->getFeedType());


    }


    /**
     * View all posts in $category_slug category
     *
     * @param Request $request
     * @param $category_slug
     * @return mixed
     */
    public function view_category($category_slug)
    {
        return $this->index($category_slug);
    }

    /**
     * View a single post and (if enabled) its comments
     *
     * @param Request $request
     * @param $blogPostSlug
     * @return mixed
     */
    public function viewSinglePost(Request $request, $blogPostSlug)
    {

        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged in user can manageb log posts
        $blog_post = BlogEtcPost::where("slug", $blogPostSlug)
            ->firstOrFail();

        $comments = $blog_post->comments()
            ->where("approved", true)
            ->orderBy("id", 'asc')
            ->limit(config("blogetc.comments.max_num_of_comments_to_show", 500))// sane limit on num of comments. No pagination (maybe todo?)
            ->with("user")
            ->get();


        /** @var CaptchaAbstract $captcha */
        $captcha = $captcha = $this->getCaptchaObject();
        if ($captcha) {
            $captcha->runCaptchaBeforeShowingPosts($request, $blog_post);
        }


        return view("blogetc::single_post", [
            'post' => $blog_post,
            'comments' => $comments,
            'captcha' => $captcha,
        ]);
    }


}
