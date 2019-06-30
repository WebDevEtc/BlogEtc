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

    /** @var BlogEtcPostsService */
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
     * @return View
     */
    public function index(): View
    {
        $posts = $this->service->indexPaginated();

        return view('blogetc_admin::index', ['posts' => $posts]);
    }

    /**
     * Show form for creating new post
     *
     * @return View
     */
    public function create(): View
    {
        return view('blogetc_admin::posts.add_post');
    }

    /**
     * Save a new post
     *
     * @param CreateBlogEtcPostRequest $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function store(CreateBlogEtcPostRequest $request) : RedirectResponse
    {
        $newBlogPost = $this->service->create($request, Auth::id());

        Helpers::flashMessage('Added post');

        return redirect($newBlogPost->editUrl());
    }

    /**
     * Show form to edit post
     *
     * @param $blogPostId
     * @return View
     */
    public function edit($blogPostId) :View
    {
        $post = $this->service->repository()->find($blogPostId);

        return view('blogetc_admin::posts.edit_post', ['post' => $post]);
    }

    /**
     * Save changes to a post
     *
     * @param UpdateBlogEtcPostRequest $request
     * @param $blogPostID
     * @return RedirectResponse
     */
    public function update(UpdateBlogEtcPostRequest $request, $blogPostID):RedirectResponse
    {
        $post = $this->service->update($blogPostID, $request);

        Helpers::flashMessage('Updated post');

        return redirect($post->editUrl());
    }

    /**
     * Delete a post - removes it from the database, does not remove any featured images associated with the blog post.
     *
     * @param DeleteBlogEtcPostRequest $request
     * @param $blogPostID
     * @return View
     * @throws Exception
     */
    public function destroy(DeleteBlogEtcPostRequest $request, $blogPostID)
    {
       [ $post, $remainingFeaturedPhotos ]  = $this->service->delete($blogPostID);



        return view('blogetc_admin::posts.deleted_post', ['deletedPost' => $post, 'remainingFeaturedPhotos' => $remainingFeaturedPhotos]);
    }
}
