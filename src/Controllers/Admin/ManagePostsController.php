<?php

namespace WebDevEtc\BlogEtc\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use RuntimeException;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Requests\CreateBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\DeleteBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Services\UploadsService;

/**
 * Class BlogEtcAdminController.
 */
class ManagePostsController extends Controller
{
    /**
     * @var UploadsService
     */
    private $uploadsService;

    /**
     * BlogEtcAdminController constructor.
     */
    public function __construct(UploadsService $uploadsService)
    {
        $this->middleware(UserCanManageBlogPosts::class);

        $this->uploadsService = $uploadsService;

        if (!is_array(config('blogetc'))) {
            throw new RuntimeException('The config/blogetc.php does not exist. Publish the vendor files for the BlogEtc package by running the php artisan publish:vendor command');
        }
    }

    /**
     * View all posts.
     *
     * @return mixed
     */
    public function index()
    {
        $posts = Post::orderBy('posted_at', 'desc')->paginate(10);

        return view('blogetc_admin::index', ['posts' => $posts]);
    }

    /**
     * Show form for creating new post.
     *
     * @return Factory|View
     */
    public function create()
    {
        return view('blogetc_admin::posts.add_post');
    }

    /**
     * @deprecated - use create() instead
     */
    public function create_post()
    {
        return $this->create();
    }

    /**
     * Save a new post.
     *
     * @throws Exception
     *
     * @return RedirectResponse|Redirector
     */
    public function store(CreateBlogEtcPostRequest $request)
    {
        $editUrl = $this->uploadsService->legacyStorePost($request);

        return redirect($editUrl);
    }

    /**
     * @deprecated use store() instead
     */
    public function store_post(CreateBlogEtcPostRequest $request)
    {
        return $this->store($request);
    }

    /**
     * Show form to edit post.
     *
     * @param $blogPostId
     *
     * @return mixed
     */
    public function edit($blogPostId)
    {
        $post = Post::findOrFail($blogPostId);

        return view('blogetc_admin::posts.edit_post', ['post' => $post]);
    }

    /**
     * @deprecated - use edit() instead
     */
    public function edit_post($blogPostId)
    {
        return $this->edit($blogPostId);
    }

    /**
     * Save changes to a post.
     *
     * This uses some legacy code. This will get refactored soon into something nicer.
     *
     * @param $blogPostId
     *
     * @throws Exception
     *
     * @return RedirectResponse|Redirector
     */
    public function update(UpdateBlogEtcPostRequest $request, $blogPostId)
    {
        $editUrl = $this->uploadsService->legacyUpdatePost($request, $blogPostId);

        return redirect($editUrl);
    }

    /**
     * @deprecated use update() instead
     */
    public function update_post(UpdateBlogEtcPostRequest $request, $blogPostId)
    {
        return $this->update($request, $blogPostId);
    }

    /**
     * Delete a post.
     *
     * @param $blogPostId
     *
     * @return mixed
     */
    public function destroy(DeleteBlogEtcPostRequest $request, $blogPostId)
    {
        $deletedPost = $this->uploadsService->legacyDestroyPost($request, $blogPostId);

        return view('blogetc_admin::posts.deleted_post')
            ->withDeletedPost($deletedPost);
    }

    /**
     * @deprecated - use destroy() instead
     */
    public function destroy_post(DeleteBlogEtcPostRequest $request, $blogPostId)
    {
        return $this->destroy($request, $blogPostId);
    }
}
