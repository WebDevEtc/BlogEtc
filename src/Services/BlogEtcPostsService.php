<?php

namespace WebDevEtc\BlogEtc\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use WebDevEtc\BlogEtc\Events\BlogPostAdded;
use WebDevEtc\BlogEtc\Events\BlogPostEdited;
use WebDevEtc\BlogEtc\Events\BlogPostWillBeDeleted;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Models\BlogEtcUploadedPhoto;
use WebDevEtc\BlogEtc\Repositories\BlogEtcPostsRepository;
use WebDevEtc\BlogEtc\Requests\BaseBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcPostRequest;

/**
 * Class BlogEtcPostsService
 *
 * Service class to handle most logic relating to BlogEtcPosts.
 *
 * Some Eloquent/DB things are in here - but query heavy method belong in the repository, accessible
 * as $this->repository, or publicly via repository()
 *
 * @package WebDevEtc\BlogEtc\Services
 */
class BlogEtcPostsService
{
    /**
     * @var BlogEtcPostsRepository
     */
    private $repository;

    public function __construct(BlogEtcPostsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * BlogEtcPosts repository - for query heavy method.
     *
     * I don't stick 100% to all queries belonging in the repo - some Eloquent
     * things are fine to have in the service where it makes sense.
     *
     * @return BlogEtcPostsRepository
     */
    public function repository(): BlogEtcPostsRepository
    {
        return $this->repository;
    }

    /**
     * Create a new BlogEtcPost entry, and process any uploaded image
     *
     * (I'm never keen on passing around entire Request objects - this will get
     * refactored out)
     *
     * @param BaseBlogEtcPostRequest $request
     * @param int|null $userID
     * @return BlogEtcPost
     * @throws Exception
     */
    public function create(BaseBlogEtcPostRequest $request, ?int $userID): BlogEtcPost
    {
        $attributes = $request->validated() + ['user_id' => $userID];

        // set default posted_at, if none were submitted
        if (empty($attributes['posted_at'])) {
            $attributes['posted_at'] = Carbon::now();
        }

        // create new instance of BlogEtcPost, hydrate it with submitted attributes:
        $newBlogPost = new BlogEtcPost($request->validated());

        // process any submitted images:
        $this->processUploadedImages($request, $newBlogPost);

        // save it:
        $newBlogPost->save();

        // sync submitted categories:
        // TODO - add interface, or add to base request b/c categories() isn't technically always be there
        $newBlogPost->categories()->sync($request->categories());

        event(new BlogPostAdded($newBlogPost));

        return $newBlogPost;
    }

    /**
     * Return all results, paginated
     *
     * @param int $perPage
     * @param int|null $categoryID
     * @return LengthAwarePaginator
     */
    public function indexPaginated($perPage = 10, int $categoryID = null): LengthAwarePaginator
    {
        return $this->repository->indexPaginated($perPage, $categoryID);
    }

    /**
     * Process any uploaded images (for featured image)
     *
     * @param BaseBlogEtcPostRequest $request
     * @param BlogEtcPost $new_blog_post
     * @todo - next full release, tidy this up!
     */
    protected function processUploadedImages(BaseBlogEtcPostRequest $request, BlogEtcPost $new_blog_post): void
    {
        if (!config('blogetc.image_upload_enabled')) {
            // image upload was disabled
            return;
        }

        // TODO - add this method - remove the comment and put this back into code
//        $this->increaseMemoryLimit();

        // to save in db later
        $uploaded_image_details = [];

        foreach ((array)config('blogetc.image_sizes') as $size => $image_size_details) {
            // TODO - add interface, or add to base request b/c get_image_file() isn't technically always be there
            if ($image_size_details['enabled'] && $photo = $request->get_image_file($size)) {
                // this image size is enabled, and
                // we have an uploaded image that we can use

                // TODO - this method does not exist
                $uploaded_image = $this->UploadAndResize(
                    $new_blog_post,
                    $new_blog_post->title,
                    $image_size_details,
                    $photo
                );

                $new_blog_post->$size = $uploaded_image['filename'];
                $uploaded_image_details[$size] = $uploaded_image;
            }
        }

        // store the image upload.
        // todo: link this to the blogetc_post row.
        if (count(array_filter($uploaded_image_details)) > 0) {
            BlogEtcUploadedPhoto::create([
                'source' => 'BlogFeaturedImage',
                'uploaded_images' => $uploaded_image_details,
            ]);
        }
    }

    /**
     * Update a BlogEtcPost with new attributes and/or images.
     *
     * N.B. I dislike sending the whole Request object around, this will get refactored.
     *
     * @param int $blogPostID
     * @param UpdateBlogEtcPostRequest $request
     * @return BlogEtcPost
     */
    public function update(int $blogPostID, UpdateBlogEtcPostRequest $request): BlogEtcPost
    {
        // get the post:
        $post = $this->repository->find($blogPostID);

        // update it:
        $post->fill($request->validated());

        // save any uploaded image:
        $this->processUploadedImages($request, $post);

        // save changes:
        $post->save();

        // sync post categories:
        $post->categories()->sync($request->categories());

        event(new BlogPostEdited($post));

        return $post;
    }

    /**
     * Delete a blog etc post, return the deleted post and an array of featured images which were assoicated
     * to the blog post (but which were not deleted form the filesystem)
     *
     * @param int $blogPostEtcID
     * @return array - [BlogEtcPost (deleted post), array (remaining featured photos)
     * @throws Exception
     */
    public function delete(int $blogPostEtcID): array
    {
        // delete the DB entry:
        $post = $this->repository->find($blogPostEtcID);

        event(new BlogPostWillBeDeleted($post));

        $post->delete();

        // now return an array of image files that are not deleted (so we can tell the user that these featured photos
        // still exist on the filesystem

        $remainingPhotos = [];

        foreach ((array)config('blogetc.image_sizes') as $imageSize => $imageSizeInfo) {
            if ($post->$imageSize) {
                $fullPath = public_path(config('blogetc.blog_upload_dir', 'blog_images') . '/' . $imageSize);

                if (file_exists($fullPath)) {
                    // there was record of this size in the db, so push it to array of featured photos which remain
                    // (Note: there is no check here to see if they actually exist on the filesystem).
                    $fileSize = filesize($fullPath);

                    // TODO - refactor to use Laravel filesystem/disks
                    if ($fileSize) {
                        // get file gt
                        //size in human readable (kb)
                        $fileSize = round(filesize($fileSize) / 1000, 1) . ' kb';
                    }

                    $remainingPhotos[] = [
                        'filename' => $post->$imageSize,
                        'full_path' => $fullPath,
                        'file_size' => $fileSize,
                        'url' => asset(config('blogetc.blog_upload_dir', 'blog_images') . '/' . $post->$imageSize),
                    ];
                }
            }
        }

        return [$post, $remainingPhotos];
    }

    /**
     * Find and return a blog post based on slug
     *
     * @param string $slug
     * @return BlogEtcPost
     */
    public function findBySlug(string $slug): BlogEtcPost
    {
        return $this->repository->findBySlug($slug);
    }
}
