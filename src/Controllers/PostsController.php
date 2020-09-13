<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use View;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Requests\SearchRequest;
use WebDevEtc\BlogEtc\Services\CaptchaService;
use WebDevEtc\BlogEtc\Services\CategoriesService;
use WebDevEtc\BlogEtc\Services\PostsService;

/**
 * Class BlogEtcReaderController
 * All of the main public facing methods for viewing blog content (index, single posts).
 */
class PostsController extends Controller
{
    /** @var PostsService */
    private $postsService;

    /** @var CategoriesService */
    private $categoriesService;

    /** @var CaptchaService */
    private $captchaService;

    /**
     * BlogEtcReaderController constructor.
     */
    public function __construct(
        PostsService $postsService,
        CategoriesService $categoriesService,
        CaptchaService $captchaService
    ) {
        $this->postsService = $postsService;
        $this->categoriesService = $categoriesService;
        $this->captchaService = $captchaService;
    }

    /**
     * Show the search results.
     */
    public function search(SearchRequest $request): \Illuminate\Contracts\View\View
    {
        // Laravel full text search (swisnl/laravel-fulltext) disabled due to poor Laravel 8 support.
        // If you wish to add it, copy the code in this method that was in commit 9aff6c37d130.

        // The LIKE query is not efficient. Search can be disabled in config.
        $searchResults = Post::where('title', 'LIKE', '%'.$request->get('s').'%')->limit(100)->get();

        // Map it so the post is actually accessible with ->indexable, for backwards compatibility in old view files.
        $searchResultsMappedWithIndexable = $searchResults->map(function (Post $post) {
            return new class($post) {
                public $indexable;

                public function __construct(Post $post)
                {
                    $this->indexable = $post;
                }
            };
        });

        return view('blogetc::search', [
            'title' => 'Search results for '.e($request->searchQuery()),
            'query' => $request->searchQuery(),
            'search_results' => $searchResultsMappedWithIndexable,
        ]);
    }

    /**
     * @deprecated - use showCategory() instead
     */
    public function view_category($categorySlug): \Illuminate\Contracts\View\View
    {
        return $this->showCategory($categorySlug);
    }

    /**
     * View posts in a category.
     */
    public function showCategory($categorySlug): \Illuminate\Contracts\View\View
    {
        return $this->index($categorySlug);
    }

    /**
     * Show blog posts
     * If $categorySlug is set, then only show from that category.
     *
     * @param string $categorySlug
     *
     * @return mixed
     */
    public function index(string $categorySlug = null)
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged
        // in user can manage log posts
        $title = config('blogetc.blog_index_title', 'Viewing blog');

        // default category ID
        $categoryID = null;

        if ($categorySlug) {
            // get the category
            $category = $this->categoriesService->findBySlug($categorySlug);

            // get category ID to send to service
            $categoryID = $category->id;

            // TODO - make configurable
            $title = config('blogetc.blog_index_category_title', 'Viewing blog posts in ').$category->category_name;
        }

        $posts = $this->postsService->indexPaginated(config('blogetc.per_page'), $categoryID);

        return view('blogetc::index', [
            'posts' => $posts,
            'title' => $title,
            'blogetc_category' => $category ?? null,
        ]);
    }

    /**
     * @deprecated - use show()
     */
    public function viewSinglePost(Request $request, string $blogPostSlug)
    {
        return $this->show($request, $blogPostSlug);
    }

    /**
     * View a single post and (if enabled) comments.
     */
    public function show(Request $request, string $postSlug): \Illuminate\View\View
    {
        $blogPost = $this->postsService->findBySlug($postSlug);

        $usingCaptcha = $this->captchaService->getCaptchaObject();

        if (null !== $usingCaptcha && method_exists($usingCaptcha, 'runCaptchaBeforeShowingPosts')) {
            $usingCaptcha->runCaptchaBeforeShowingPosts($request, $blogPost);
        }

        return view(
            'blogetc::single_post',
            [
                'post' => $blogPost,
                'captcha' => $usingCaptcha,
                'comments' => $blogPost->comments->load('user'),
            ]
        );
    }
}
