<?php

namespace WebDevEtc\BlogEtc\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Requests\PostRequest;
use WebDevEtc\BlogEtc\Services\PostsService;

/**
 * Class ManagePostsController.
 */
class ManagePostsController extends Controller
{
    /** @var PostsService */
    private $service;

    /**
     * BlogEtcAdminController constructor.
     */
    public function __construct(PostsService $blogEtcPostsService)
    {
        $this->service = $blogEtcPostsService;

        if (!is_array(config('blogetc'))) {
            throw new RuntimeException('The config/blogetc.php does not exist. Publish the vendor files for the BlogEtc'.' package by running the php artisan publish:vendor command');
        }
    }

    /**
     * View all posts (paginated).
     */
    public function index(): View
    {
        $posts = $this->service->indexPaginated();

        return view('blogetc_admin::index', ['posts' => $posts]);
    }

    /**
     * Show form for creating new post.
     */
    public function create(): View
    {
        return view('blogetc_admin::posts.add_post', ['post' => new Post()]);
    }

    /**
     * Save a new post.
     *
     * @throws Exception
     */
    public function store(PostRequest $request): RedirectResponse
    {
        $newBlogPost = $this->service->create($request, Auth::id());

        Helpers::flashMessage('Added post');

        return redirect($newBlogPost->editUrl());
    }

    /**
     * Show form to edit post.
     *
     * @param $blogPostId
     */
    public function edit(int $blogPostId): View
    {
        $blogPost = $this->service->repository()->find($blogPostId);

        return view('blogetc_admin::posts.edit_post', ['post' => $blogPost]);
    }

    /**
     * Save changes to a post.
     *
     * @throws Exception
     */
    public function update(PostRequest $request, int $blogPostID): RedirectResponse
    {
        $blogPost = $this->service->update($blogPostID, $request);

        Helpers::flashMessage('Updated post');

        return redirect($blogPost->editUrl());
    }

    /**
     * Delete a post - removes it from the database, does not remove any featured images associated with the blog post.
     *
     * @throws Exception
     */
    public function destroy(PostRequest $request, int $blogPostID): View
    {
        [$blogPost, $remainingPhotos] = $this->service->delete($blogPostID);

        return view(
            'blogetc_admin::posts.deleted_post',
            ['deletedPost' => $blogPost, 'remainingPhotos' => $remainingPhotos]
        );
    }
}
