<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LogicException;
use Swis\LaravelFulltext\Search;
use WebDevEtc\BlogEtc\Captcha\UsesCaptcha;
use WebDevEtc\BlogEtc\Requests\SearchRequest;
use WebDevEtc\BlogEtc\Services\BlogEtcCategoriesService;
use WebDevEtc\BlogEtc\Services\BlogEtcPostsService;
use WebDevEtc\BlogEtc\Services\CaptchaService;

/**
 * Class BlogEtcReaderController
 * All of the main public facing methods for viewing blog content (index, single posts)
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcReaderController extends Controller
{
    use UsesCaptcha;

    /** @var BlogEtcPostsService */
    private $postsService;
    /** @var BlogEtcCategoriesService */
    private $categoriesService;
    /** @var CaptchaService */
    private $captchaService;

    public function __construct(
        BlogEtcPostsService $postsService,
        BlogEtcCategoriesService $categoriesService,
        CaptchaService $captchaService
    ) {
        $this->postsService = $postsService;
        $this->categoriesService = $categoriesService;
        $this->captchaService = $captchaService;
    }

    /**
     * Show the search results
     *
     * @param SearchRequest $request
     * @return \Illuminate\Contracts\View\View
     */
    public function search(SearchRequest $request): \Illuminate\Contracts\View\View
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
     * @param $categorySlug
     * @return \Illuminate\Contracts\View\View
     */
    public function showCategory($categorySlug): \Illuminate\Contracts\View\View
    {
        return $this->index($categorySlug);
    }

    /**
     * Show blog posts
     * If $categorySlug is set, then only show from that category
     *
     * @param string $categorySlug
     * @return mixed
     */
    public function index(string $categorySlug = null)
    {
        // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the logged
        // in user can manage log posts
        // todo - set these in config
        $title = 'Viewing Blog'; // default title...

        if ($categorySlug) {
            // get the category
            $category = $this->categoriesService->findBySlug($categorySlug);

            // get category ID to send to service
            $categoryID = $category->id;

            $title = 'Viewing blog posts in ' . $category->category_name;
        }

        $posts = $this->postsService->indexPaginated(10, $categoryID ?? null);

        return view('blogetc::index', [
            'posts' => $posts,
            'title' => $title,
            'category' => $category ?? null,
        ]);
    }

    /**
     * View a single post and (if enabled) comments
     *
     * @param Request $request
     * @param $postSlug
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $postSlug): \Illuminate\View\View
    {
        $post = $this->postsService->findBySlug($postSlug);

        // if using captcha, there might be some code to run now or to echo in the view:
        $usingCaptcha = $this->captchaService->getCaptchaObject();

        if ($usingCaptcha !== null) {
            $usingCaptcha->runCaptchaBeforeShowingPosts($request, $post);
        }

        return view(
            'blogetc::single_post',
            [
                'post' => $post,
                // the default scope only selects approved comments, ordered by id
                'comments' => $post->comments()->with('user')->get(),
                'captcha' => $usingCaptcha,
            ]
        );
    }

}
