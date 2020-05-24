<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Swis\Laravel\Fulltext\Search;
use View;
use WebDevEtc\BlogEtc\Captcha\UsesCaptcha;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class BlogEtcReaderController
 * All of the main public facing methods for viewing blog content (index, single posts).
 */
class BlogEtcReaderController extends Controller
{
    use UsesCaptcha;

    /**
     * Show the search results for $_GET['s'].
     *
     * @throws Exception
     *
     * @return Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        if (!config('blogetc.search.search_enabled')) {
            throw new Exception('Search is disabled');
        }
        $query = $request->get('s');
        $search = new Search();
        $search_results = $search->run($query);

        View::share('title', 'Search results for '.e($query));

        return view('blogetc::search', ['query' => $query, 'search_results' => $search_results]);
    }

    /**
     * View all posts in $category_slug category.
     *
     * @param Request $request
     * @param $category_slug
     *
     * @return mixed
     */
    public function view_category($category_slug)
    {
        return $this->index($category_slug);
    }

    /**
     * Show blog posts
     * If category_slug is set, then only show from that category.
     *
     * @param null $category_slug
     *
     * @return mixed
     */
    public function index($category_slug = null)
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope,
        // and don't take effect if the logged in user can manage log posts
        $title = 'Viewing blog';

        if ($category_slug) {
            $category = Category::where('slug', $category_slug)->firstOrFail();
            $posts = $category->posts()->where('blog_etc_post_categories.blog_etc_category_id', $category->id);

            // at the moment we handle this special case (viewing a category) by hard coding in the following two lines.
            // You can easily override this in the view files.
            View::share('blogetc_category', $category);
            $title = 'Viewing posts in '.$category->category_name.' category';
        } else {
            $posts = Post::query();
        }

        $posts = $posts->orderBy('posted_at', 'desc')
            ->where('is_published', '=', 1)
            ->where('posted_at', '<', Carbon::now())
            ->orderBy('posted_at', 'desc')
            ->paginate(config('blogetc.per_page', 10));

        return view('blogetc::index', [
            'posts' => $posts,
            'title' => $title,
        ]);
    }

    /**
     * View a single post and (if enabled) it's comments.
     *
     * @param $blogPostSlug
     *
     * @return mixed
     */
    public function viewSinglePost(Request $request, $blogPostSlug)
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope,
        // and don't take effect if the logged in user can manage log posts
        $blog_post = Post::where('slug', $blogPostSlug)
            ->firstOrFail();

        if ($captcha = $this->getCaptchaObject()) {
            $captcha->runCaptchaBeforeShowingPosts($request, $blog_post);
        }

        return view('blogetc::single_post', [
            'post' => $blog_post,
            // the default scope only selects approved comments, ordered by id
            'comments' => $blog_post->comments()
                ->with('user')
                ->get(),
            'captcha' => $captcha,
        ]);
    }
}
