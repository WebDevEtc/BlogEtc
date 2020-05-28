<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LogicException;
use Swis\Laravel\Fulltext\Search;
use View;
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
        if (! config('blogetc.search.search_enabled')) {
            throw new LogicException('Search is disabled');
        }

        $query = $request->searchQuery();

        $search = new Search();
        $searchResults = $search->run($query);

        return view('blogetc::search', [
            'title'          => 'Search results for '.e($query),
            'query'          => $query,
            'search_results' => $searchResults,
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
//        $title = config('blogetc.blog_index_title'); // default title...
        $title = 'Viewing blog';

        // default category ID
        $categoryID = null;

        if ($categorySlug) {
            // get the category
            $category = $this->categoriesService->findBySlug($categorySlug);

            // get category ID to send to service
            $categoryID = $category->id;

            // TODO - make configurable
            $title = 'Viewing blog posts in '.$category->category_name;
        }

        $posts = $this->postsService->indexPaginated(10, $categoryID);

        return view('blogetc::index', [
            'posts'            => $posts,
            'title'            => $title,
            'blogetc_category' => $category ?? null,
        ]);
    }

    /**
     * @deprecated - use show()
     */
    public function viewSinglePost(Request $request, $blogPostSlug)
    {
        return $this->show($request, $blogPostSlug);
    }

    /**
     * View a single post and (if enabled) comments.
     *
     * @param $postSlug
     */
    public function show(Request $request, $postSlug): \Illuminate\View\View
    {
        $blogPost = $this->postsService->findBySlug($postSlug);

        $usingCaptcha = $this->captchaService->getCaptchaObject();

        if (null !== $usingCaptcha && method_exists($usingCaptcha, 'runCaptchaBeforeShowingPosts')) {
            $usingCaptcha->runCaptchaBeforeShowingPosts($request, $blogPost);
        }

        return view(
            'blogetc::single_post',
            [
                'post'     => $blogPost,
                'captcha'  => $usingCaptcha,
                'comments' => $blogPost->comments->load('user'),
            ]
        );
    }
}
