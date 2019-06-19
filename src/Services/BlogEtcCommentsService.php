<?php

namespace WebDevEtc\BlogEtc\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use WebDevEtc\BlogEtc\Events\BlogPostAdded;
use WebDevEtc\BlogEtc\Events\BlogPostEdited;
use WebDevEtc\BlogEtc\Events\BlogPostWillBeDeleted;
use WebDevEtc\BlogEtc\Events\CategoryAdded;
use WebDevEtc\BlogEtc\Events\CategoryEdited;
use WebDevEtc\BlogEtc\Events\CategoryWillBeDeleted;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Models\BlogEtcUploadedPhoto;
use WebDevEtc\BlogEtc\Repositories\BlogEtcCategoriesRepository;
use WebDevEtc\BlogEtc\Repositories\BlogEtcCommentsRepository;
use WebDevEtc\BlogEtc\Repositories\BlogEtcPostsRepository;
use WebDevEtc\BlogEtc\Requests\BaseBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcPostRequest;

/**
 * Class BlogEtcCategoriesService
 *
 * Service class to handle most logic relating to BlogEtcCategory entries.
 *
 * Some Eloquent/DB things are in here - but query heavy method belong in the repository, accessible
 * as $this->repository, or publicly via repository()
 *
 * @package WebDevEtc\BlogEtc\Services
 */
class BlogEtcCommentsService
{
    /**
     * @var BlogEtcCommentsRepository
     */
    private $repository;

    public function __construct(BlogEtcCommentsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * BlogEtcCategoriesRepository repository - for query heavy method.
     *
     * I don't stick 100% to all queries belonging in the repo - some Eloquent
     * things are fine to have in the service where it makes sense.
     *
     */
    public function repository(): BlogEtcCommentsRepository
    {
        return $this->repository;
    }

    /**
     * @param bool $includeUnapproved
     * @return Collection
     */
    public function all($includeUnapproved = false):Collection
    {

    }
//
//    /**
//     * Create a new BlogEtcCategory entry
//     *
//     * @param array $attributes
//     * @return BlogEtcCategory
//     */
//    public function create(array $attributes): BlogEtcCategory
//    {
//        $new_category = new BlogEtcCategory($attributes);
//        $new_category->save();
//
//
//        event(new CategoryAdded($new_category));
//    }
//
//    /**
//     * Update a blog etc category entry
//     *
//     * @param int $categoryID
//     * @param array $attributes
//     * @return BlogEtcCategory
//     */
//    public function update(int $categoryID, array $attributes):BlogEtcCategory
//    {
//        /** @var BlogEtcCategory $category */
//        $category = BlogEtcCategory::findOrFail($categoryID);
//        $category->fill($attributes);
//        $category->save();
//
//        event(new CategoryEdited($category));
//
//        return $category;
//    }
//
//    /**
//     * Delete a BlogEtcCategory
//     *
//     * @param int $categoryID
//     */
//    public function delete(int $categoryID):void
//    {
//        $category = BlogEtcCategory::findOrFail($categoryID);
//        event(new CategoryWillBeDeleted($category));
//        $category->delete();
//    }

}
