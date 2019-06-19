<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use LogicException;
use Swis\LaravelFulltext\Search;
use View;
use WebDevEtc\BlogEtc\Captcha\UsesCaptcha;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\SearchRequest;

/**
 * Class BlogEtcReaderController
 * All of the main public facing methods for viewing blog content (index, single posts)
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcReaderController extends Controller
{
    use UsesCaptcha;

    /**
     * Show the search results
     *
     * @param SearchRequest $request
     * @return \Illuminate\View\View
     */
    public function search(SearchRequest $request):\Illuminate\View\View
    {
        if (!config('blogetc.search.search_enabled')) {
            throw new LogicException('Search is disabled');
        }

        $query = $request->query();

        $search = new Search();
        $search_results = $search->run($query);

        return view('blogetc::search', [
            'title' => 'Search results for ' . e($query),
            'query' => $query,
            'search_results' => $search_results,
        ]);
    }

    /**
     * View all posts in $category_slug category
     *
     * @param $category_slug
     * @return mixed
     */
    public function showCategory($category_slug)
    {
        return $this->index($category_slug);
    }

    /**
     * Show blog posts
     * If category_slug is set, then only show from that category
     *
     * @param null $category_slug
     * @return mixed
     */
    public function index($category_slug = null)
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged
        // in user can manage log posts
        $title = 'Viewing blog'; // default title...

        if ($category_slug) {
            $category = BlogEtcCategory::where('slug', $category_slug)->firstOrFail();
            $posts = $category->posts()->where('blog_etc_post_categories.blog_etc_category_id', $category->id);

            // at the moment we handle this special case (viewing a category) by hard coding in the following two lines.
            // You can easily override this in the view files.
            View::share('blogetc_category',
                $category); // so the view can say "You are viewing $CATEGORY_NAME category posts"
            $title = 'Viewing posts in ' . $category->category_name . ' category'; // hardcode title here...
        } else {
            $posts = BlogEtcPost::query();
        }

        $posts = $posts->orderBy('posted_at', 'desc')
            ->paginate(config('blogetc.per_page', 10));

        return view('blogetc::index', [
            'posts' => $posts,
            'title' => $title,
        ]);
    }

    /**
     * View a single post and (if enabled) it's comments
     *
     * @param Request $request
     * @param $blogPostSlug
     * @return mixed
     */
    public function show(Request $request, $blogPostSlug): \Illuminate\View\View
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged in user can manage log posts
        $blog_post = BlogEtcPost::where('slug', $blogPostSlug)
            ->firstOrFail();

        $usingCaptcha = $usingCaptcha = $this->getCaptchaObject();

        if ($usingCaptcha !== null) {
            $usingCaptcha->runCaptchaBeforeShowingPosts($request, $blog_post);
        }

        return view(
            'blogetc::single_post',
            [
                'post' => $blog_post,
                // the default scope only selects approved comments, ordered by id
                'comments' => $blog_post->comments()->with('user')->get(),
                'captcha' => $usingCaptcha,
            ]
        );
    }

}
