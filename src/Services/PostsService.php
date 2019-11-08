<?php

namespace WebDevEtc\BlogEtc\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use WebDevEtc\BlogEtc\Events\BlogPostAdded;
use WebDevEtc\BlogEtc\Events\BlogPostEdited;
use WebDevEtc\BlogEtc\Events\BlogPostWillBeDeleted;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Repositories\PostsRepository;
use WebDevEtc\BlogEtc\Requests\PostRequest;

/**
 * Class BlogEtcPostsService.
 *
 * Service class to handle most logic relating to BlogEtcPosts.
 *
 * Some Eloquent/DB things are in here - but query heavy method belong in the repository, accessible
 * as $this->repository, or publicly via repository()
 */
class PostsService
{
    /** @var PostsRepository */
    private $repository;

    /** @var UploadsService */
    private $uploadsService;

    /**
     * PostsService constructor.
     *
     * @param PostsRepository $repository
     * @param UploadsService $uploadsService
     */
    public function __construct(PostsRepository $repository, UploadsService $uploadsService)
    {
        $this->repository = $repository;
        $this->uploadsService = $uploadsService;
    }

    /**
     * BlogEtcPosts repository - for query heavy method.
     *
     * I don't stick 100% to all queries belonging in the repo - some Eloquent
     * things are fine to have in the service where it makes sense.
     *
     * @return PostsRepository
     */
    public function repository(): PostsRepository
    {
        return $this->repository;
    }

    /**
     * Create a new BlogEtcPost entry, and process any uploaded image.
     *
     * (I'm never keen on passing around entire Request objects - this will get
     * refactored out)
     *
     * @param PostRequest $request
     * @param int|null $userID
     *
     * @return Post
     * @throws Exception
     */
    public function create(PostRequest $request, ?int $userID): Post
    {
        $attributes = $request->validated() + ['user_id' => $userID];

        // set default posted_at, if none were submitted
        if (empty($attributes['posted_at'])) {
            $attributes['posted_at'] = Carbon::now();
        }

        // Create new instance of BlogEtcPost, hydrate it with submitted attributes:
        // Must save it first, then process images (so they can be linked to the blog post) then update again with the
        // featured images. This isn't ideal, but seeing as blog posts are not created very often it isn't too bad...
        $newBlogPost = $this->repository->create($request->validated());

        // process any submitted images:
        if (config('blogetc.image_upload_enabled')) {
            // image upload was enabled - handle uploading of any new images:
            $uploadedImages = $this->uploadsService->processFeaturedUpload($request, $newBlogPost);
            $this->repository->updateImageSizes($newBlogPost, $uploadedImages);
        }

        // sync submitted categories:
        if (count($request->categories())) {
            $newBlogPost->categories()->sync($request->categories());
        }

        event(new BlogPostAdded($newBlogPost));

        return $newBlogPost;
    }

    /**
     * Return all results, paginated.
     *
     * @param int $perPage
     * @param int|null $categoryID
     *
     * @return LengthAwarePaginator
     */
    public function indexPaginated($perPage = 10, int $categoryID = null): LengthAwarePaginator
    {
        return $this->repository->indexPaginated($perPage, $categoryID);
    }

    /**
     * Return posts for rss feed.
     *
     * @return Collection|Post[]
     */
    public function rssItems(): Collection
    {
        return $this->repository->rssItems();
    }

    /**
     * Update a BlogEtcPost with new attributes and/or images.
     *
     * N.B. I dislike sending the whole Request object around, this will get refactored.
     *
     * Does not currently use repo calls - works direct on Eloquent. This will change.
     *
     * @param int $blogPostID
     * @param PostRequest $request
     *
     * @return Post
     * @throws Exception
     */
    public function update(int $blogPostID, PostRequest $request): Post
    {
        // get the post:
        $post = $this->repository->find($blogPostID);

        // update it:
        $post->fill($request->validated());

        // save any uploaded image:
        // TODO - copy logic from create! this is now wrong
        $this->uploadsService->processFeaturedUpload($request, $post);

        // save changes:
        $post->save();

        // sync post categories:
        $post->categories()->sync($request->categories());

        event(new BlogPostEdited($post));

        return $post;
    }

    /**
     * Delete a blog etc post, return the deleted post and an array of featured images which were associated
     * to the blog post (but which were not deleted form the filesystem).
     *
     * @todo - rewrite delete() to use a repo call.
     *
     * @param int $postID
     *
     * @return array - [Post (deleted post), array (remaining featured photos)
     * @throws Exception
     */
    public function delete(int $postID): array
    {
        // delete the DB entry:
        $post = $this->repository->find($postID);

        event(new BlogPostWillBeDeleted($post));

        $post->delete();

        // now return an array of image files that are not deleted (so we can tell the user that these featured photos
        // still exist on the filesystem

        $remainingPhotos = [];

        foreach ((array) config('blogetc.image_sizes') as $imageSize => $imageSizeInfo) {
            if ($post->$imageSize) {
                $fullPath = public_path(config('blogetc.blog_upload_dir', 'blog_images').'/'.$imageSize);

                if (file_exists($fullPath)) {
                    // there was record of this size in the db, so push it to array of featured photos which remain
                    // (Note: there is no check here to see if they actually exist on the filesystem).
                    $fileSize = filesize($fullPath);

                    // TODO - refactor to use Laravel filesystem/disks
                    if ($fileSize) {
                        // get file gt
                        //size in human readable (kb)
                        $fileSize = $this->getFileSize($fileSize);
                    }

                    $remainingPhotos[] = [
                        'filename' => $post->$imageSize,
                        'full_path' => $fullPath,
                        'file_size' => $fileSize,
                        'url' => asset(config('blogetc.blog_upload_dir', 'blog_images').'/'.$post->$imageSize),
                    ];
                }
            }
        }

        return [$post, $remainingPhotos];
    }

    /**
     * Find and return a blog post based on slug.
     *
     * @param string $slug
     *
     * @return Post
     */
    public function findBySlug(string $slug): Post
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Get human readable file size (in kb).
     *
     * @param int $fileSize
     * @return string
     */
    protected function getFileSize(int $fileSize): string
    {
        return round(filesize($fileSize) / 1000, 1).' kb';
    }
}
