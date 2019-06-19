<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use RuntimeException;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Requests\CreateBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\DeleteBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Services\BlogEtcPostsService;
use WebDevEtc\BlogEtc\Traits\UploadFileTrait;

/**
 * Class BlogEtcAdminController
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcAdminController extends Controller
{
    use UploadFileTrait;
    /**
     * @var BlogEtcPostsService
     */
    private $service;

    /**
     * BlogEtcAdminController constructor.
     * @param BlogEtcPostsService $blogEtcPostsService
     */
    public function __construct(BlogEtcPostsService $blogEtcPostsService)
    {
        $this->service = $blogEtcPostsService;

        $this->middleware(UserCanManageBlogPosts::class);

        if (!is_array(config('blogetc'))) {
            throw new RuntimeException(
                'The config/blogetc.php does not exist. Publish the vendor files for the BlogEtc' .
                ' package by running the php artisan publish:vendor command'
            );
        }
    }

    /**
     * View all posts (paginated)
     *
     * @return mixed
     */
    public function index(): View
    {
        $posts = $this->service->indexPaginated();

        return view('blogetc_admin::index', ['posts' => $posts]);
    }

    /**
     * Show form for creating new post
     */
    public function create(): View
    {
        // show the create new post form
        return view('blogetc_admin::posts.add_post');
    }

    /**
     * Save a new post
     *
     * @param CreateBlogEtcPostRequest $request
     * @return RedirectResponse|Redirector
     * @throws Exception
     */
    public function store(CreateBlogEtcPostRequest $request)
    {
        $newBlogPost = $this->service->create($request, Auth::id());

        Helpers::flash_message('Added post');

        return redirect($newBlogPost->edit_url());
    }

    /**
     * Show form to edit post
     *
     * @param $blogPostId
     * @return mixed
     */
    public function edit($blogPostId)
    {
        $post = $this->service->repository()->find($blogPostId);

        return view('blogetc_admin::posts.edit_post', ['post' => $post]);
    }

    /**
     * Save changes to a post
     *
     * @param UpdateBlogEtcPostRequest $request
     * @param $blogPostID
     * @return RedirectResponse|Redirector
     */
    public function update(UpdateBlogEtcPostRequest $request, $blogPostID)
    {
        $post = $this->service->update($blogPostID, $request);

        Helpers::flash_message('Updated post');

        return redirect($post->edit_url());
    }

    /**
     * Delete a post
     *
     * @param DeleteBlogEtcPostRequest $request
     * @param $blogPostID
     * @return mixed
     * @throws Exception
     */
    public function destroy(DeleteBlogEtcPostRequest $request, $blogPostID)
    {
        $post = $this->service->delete($blogPostID);

        // todo - delete the featured images?
        // At the moment it just issues a warning saying the images are still on the server.

        return view('blogetc_admin::posts.deleted_post', ['deletedPost' => $post]);
    }
}
