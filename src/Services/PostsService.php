<?php

namespace WebDevEtc\BlogEtc\Services;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use LogicException;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Repositories\PostsRepository;

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
     */
    public function repository(): PostsRepository
    {
        return $this->repository;
    }

//
//    /**
//     * Create a new BlogEtcPost entry, and process any uploaded image.
//     *
//     * (I'm never keen on passing around entire Request objects - this will get
//     * refactored out)
//     *
//     * @throws Exception
//     */
//    public function create(PostRequest $request, ?int $userID): Post
//    {
//        throw new LogicException('PostsService create is not yet ready for use');
//        $attributes = $request->validated() + ['user_id' => $userID];
//
//        // set default posted_at, if none were submitted
//        if (empty($attributes['posted_at'])) {
//            $attributes['posted_at'] = Carbon::now();
//        }
//
//        $newBlogPost = $this->repository->create($request->validated());
//
//        if (config('blogetc.image_upload_enabled')) {
//            $uploadedImages = $this->uploadsService->processFeaturedUpload($request, $newBlogPost);
//            $this->repository->updateImageSizes($newBlogPost, $uploadedImages);
//        }
//
//        if (count($request->categories())) {
//            $newBlogPost->categories()->sync($request->categories());
//        }
//
//        event(new BlogPostAdded($newBlogPost));
//
//        return $newBlogPost;
//    }

    /**
     * Return all results, paginated.
     *
     * @param int $perPage
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
     * @throws Exception
     */
    public function update(/** @scrutinizer ignore-unused */ int $blogPostID, /*PostRequest*/ /** @scrutinizer ignore-unused */ $request): Post
    {
        throw new LogicException('PostsService update is not yet ready for use');
//        $post = $this->repository->find($blogPostID);
//
//        // TODO - split this into a repo call.
//        $post->fill($request->validated());
//
//        // TODO - copy logic from create! this is now wrong
//        $this->uploadsService->processFeaturedUpload($request, $post);
//
//        $post->save();
//
//        $post->categories()->sync($request->categories());
//
//        event(new BlogPostEdited($post));
//
//        return $post;
    }

    /**
     * Delete a blog etc post, return the deleted post and an array of featured images which were associated
     * to the blog post (but which were not deleted form the filesystem).
     *
     * @throws Exception
     *
     * @return array - [Post (deleted post), array (remaining featured photos)
     */
    public function delete(int $postID): array
    {
        throw new LogicException('PostsService delete is not yet ready for use');
//        $post = $this->repository->find($postID);
//
//        event(new BlogPostWillBeDeleted($post));
//
//        $this->repository->delete($postID);
//
//        $remainingPhotos = [];
//
//        foreach ((array) config('blogetc.image_sizes') as $imageSize => $imageSizeInfo) {
//            if ($post->$imageSize) {
//                $fullPath = config('blogetc.blog_upload_dir', 'blog_images').'/'.$post->$imageSize;
//
//                $fileSize = UploadsService::disk()->getSize($fullPath);
//
//                if (false !== $fileSize) {
//                    $fileSize = $this->humanReadableFileSize($fileSize);
//                }
//
//                $remainingPhotos[] = [
//                    'filename'  => $post->$imageSize,
//                    'full_path' => $fullPath,
//                    'file_size' => $fileSize,
//                    'url'       => UploadsService::disk()->url($fullPath),
//                ];
//            }
//        }
//
//        return [$post, $remainingPhotos];
    }

    /**
     * Get human readable file size (in kb).
     */
    protected function humanReadableFileSize(int $fileSize): string
    {
        return round($fileSize / 1000, 1).' kb';
    }

    /**
     * Find and return a blog post based on slug.
     */
    public function findBySlug(string $slug): Post
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Search for posts.
     */
    public function search(string $query, $max = 25): Collection
    {
        return $this->repository->search($query, $max);
    }
}
