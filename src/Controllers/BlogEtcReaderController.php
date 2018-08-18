<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\FeedRequest;

class BlogEtcReaderController extends Controller
{


    public function index(Request $request, $category_slug = null)
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged in user can manageb log posts
        $posts = BlogEtcPost::orderBy("posted_at", "desc");
        $title = 'Viewing blog';

        if ($category_slug) {
            $category = BlogEtcCategory::where("slug", $category_slug)->firstOrFail();
            $posts = $posts->join("blog_etc_post_categories", "blog_etc_post_categories.blog_etc_post_id",
                'blog_etc_posts.id')
                ->where("blog_etc_post_categories.blog_etc_category_id", $category->id);
            \View::share('blogetc_category',$category);
            $title = 'Viewing posts in ' . $category->category_name . " category";
        }

        $posts = $posts->select("blog_etc_posts.*")
            ->paginate(config("blogetc.per_page", 10));

        return view("blogetc::index")->withPosts($posts)->withTitle($title);
    }

    public function feed(FeedRequest $request)
    {


        $feed = \App::make("feed");
        $feed->setCache(config("blogetc.rssfeed.cache_in_minutes", 60), "blog-" . $request->getFeedType());

        if (!$feed->isCached()) {


            // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged in user can manageb log posts
            $posts = BlogEtcPost::orderBy("posted_at", "desc")
                ->limit(10)

//                            ->where( "posted_at" , "<=" , Carbon::now() )
                ->get();



            // set your feed's title, description, link, pubdate and language
            $feed->title = config("app.name") . ' Blog';
            $feed->description = config("blogetc.rssfeed.description", "Our blog RSS feed");
//            $feed->logo        = public_path( "publicfrontend/images/logo-dark.png" );
            $feed->link = route('blogetc.index');
            $feed->setDateFormat('datetime'); // 'datetime', 'timestamp' or 'carbon'

            if (isset($posts[0])) {
                $feed->pubdate = $posts[0]->posted_at;
            } else {
                // fallback
                $feed->pubdate = Carbon::now()->subYear(10);
            }

            $feed->lang = config("blogetc.rssfeed.language", "en");
            $feed->setShortening(true); // true or false
            $feed->setTextLimit(100); // maximum length of description text

            foreach ($posts as $post) {
                $feed->add($post->title,
                    $post->author_name,
                    $post->url(),
                    $post->posted_at,
                    $post->subtitle,
                    strip_tags($post->html)
                );
            }

        }

        return $feed->render($request->getFeedType());


    }


    public function view_category(Request $request, $category_slug)
    {
        return $this->index($request, $category_slug);
    }

    public function viewSinglePost(Request $request, $blogPostSlug)
    {

        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged in user can manageb log posts
        $blog_post = BlogEtcPost::where("slug", $blogPostSlug)
            ->firstOrFail();

//        dump($blog_post->categories);

//        dd("EOF");

        return view("blogetc::single_post")->withPost($blog_post);
    }

}
