<?php

namespace WebDevEtc\BlogEtc\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use WebDevEtc\BlogEtc\Events\CategoryAdded;
use WebDevEtc\BlogEtc\Events\CategoryEdited;
use WebDevEtc\BlogEtc\Events\CategoryWillBeDeleted;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Repositories\BlogEtcCategoriesRepository;

/**
 * Class BlogEtcCategoriesService
 *
 * Service class to handle most logic relating to BlogEtcCategory entries.
 *
 * @package WebDevEtc\BlogEtc\Services
 */
class BlogEtcCategoriesService
{

    /**
     * @var BlogEtcCategoriesRepository
     */
    private $repository;

    public function __construct(BlogEtcCategoriesRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function indexPaginated(int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->indexPaginated($perPage);
    }

    /**
     * Find and return a blog etc category from it's ID
     *
     * @param int $categoryID
     * @return BlogEtcCategory
     */
    public function find(int $categoryID): BlogEtcCategory
    {
        return $this->repository->find($categoryID);
    }

    /**
     * Find and return a blog etc category, based on its slug
     *
     * @param string $categorySlug
     * @return BlogEtcCategory
     */
    public function findBySlug(string $categorySlug): BlogEtcCategory
    {
        return $this->repository->findBySlug($categorySlug);
    }

    /**
     * Create a new BlogEtcCategory entry
     *
     * @param array $attributes
     * @return BlogEtcCategory
     */
    public function create(array $attributes): BlogEtcCategory
    {
        $newCategory = new BlogEtcCategory($attributes);
        $newCategory->save();

        event(new CategoryAdded($newCategory));

        return $newCategory;
    }

    /**
     * Update a blog etc category entry
     *
     * @param int $categoryID
     * @param array $attributes
     * @return BlogEtcCategory
     */
    public function update(int $categoryID, array $attributes): BlogEtcCategory
    {
        $category= $this->find($categoryID);
        $category->fill($attributes);
        $category->save();

        event(new CategoryEdited($category));

        return $category;
    }

    /**
     * Delete a BlogEtcCategory
     *
     * @param int $categoryID
     * @throws \Exception
     */
    public function delete(int $categoryID): void
    {
        $category= $this->find($categoryID);

        event(new CategoryWillBeDeleted($category));

        $category->delete();
    }

}
