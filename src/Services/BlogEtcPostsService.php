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
use WebDevEtc\BlogEtc\Repositories\BlogEtcCategoriesRepository;
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
    /**
     * @var BlogEtcCategoriesRepository
     */
    private $categoriesRepository;

    public function __construct(BlogEtcPostsRepository $repository, BlogEtcCategoriesRepository $categoriesRepository)
    {
        $this->repository = $repository;
        $this->categoriesRepository = $categoriesRepository;
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
    public function create(BaseBlogEtcPostRequest $request, ?int $userID)
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
        $newBlogPost->categories()->sync($request->categories());

        event(new BlogPostAdded($newBlogPost));

        return $newBlogPost;
    }

    /**
     * Return all results, paginated
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function indexPaginated($perPage = 10, int $categoryID=null): LengthAwarePaginator
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

        $this->increaseMemoryLimit();

        // to save in db later
        $uploaded_image_details = [];

        foreach ((array)config('blogetc.image_sizes') as $size => $image_size_details) {

            if ($image_size_details['enabled'] && $photo = $request->get_image_file($size)) {
                // this image size is enabled, and
                // we have an uploaded image that we can use

                $uploaded_image = $this->UploadAndResize($new_blog_post, $new_blog_post->title, $image_size_details,
                    $photo);

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
     * Delete a blog etc post, return the deleted post
     *
     * @param int $blogPostEtcID
     * @return BlogEtcPost (deleted post)
     * @throws Exception
     */
    public function delete(int $blogPostEtcID)
    {
        $post = $this->repository->find($blogPostEtcID);

        event(new BlogPostWillBeDeleted($post));

        $post->delete();

        return $post;
    }

    /**
     * Find and return a blog post based on slug
     *
     * @param string $slug
     * @return BlogEtcPost
     */
    public function findBySlug(string $slug):BlogEtcPost
    {
        return $this->repository->findBySlug($slug);

    }
}
