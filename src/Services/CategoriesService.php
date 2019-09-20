<?php

namespace WebDevEtc\BlogEtc\Services;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use WebDevEtc\BlogEtc\Events\CategoryAdded;
use WebDevEtc\BlogEtc\Events\CategoryEdited;
use WebDevEtc\BlogEtc\Events\CategoryWillBeDeleted;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Repositories\CategoriesRepository;

/**
 * Class BlogEtcCategoriesService.
 *
 * Service class to handle most logic relating to BlogEtcCategory entries.
 */
class CategoriesService
{
    /**
     * @var CategoriesRepository
     */
    private $repository;

    public function __construct(CategoriesRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $perPage
     *
     * @return LengthAwarePaginator
     */
    public function indexPaginated(int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->indexPaginated($perPage);
    }

    /**
     * Find and return a blog etc category from it's ID.
     *
     * @param int $categoryID
     *
     * @return Category
     */
    public function find(int $categoryID): Category
    {
        return $this->repository->find($categoryID);
    }

    /**
     * Find and return a blog etc category, based on its slug.
     *
     * @param string $categorySlug
     *
     * @return Category
     */
    public function findBySlug(string $categorySlug): Category
    {
        return $this->repository->findBySlug($categorySlug);
    }

    /**
     * Create a new BlogEtcCategory entry.
     *
     * @param array $attributes
     *
     * @return Category
     */
    public function create(array $attributes): Category
    {
        $newCategory = new Category($attributes);
        $newCategory->save();

        event(new CategoryAdded($newCategory));

        return $newCategory;
    }

    /**
     * Update a blog etc category entry.
     *
     * @param int   $categoryID
     * @param array $attributes
     *
     * @return Category
     */
    public function update(int $categoryID, array $attributes): Category
    {
        $category = $this->find($categoryID);
        $category->fill($attributes);
        $category->save();

        event(new CategoryEdited($category));

        return $category;
    }

    /**
     * Delete a BlogEtcCategory.
     *
     * @param int $categoryID
     *
     * @throws Exception
     */
    public function delete(int $categoryID): void
    {
        $category = $this->find($categoryID);

        event(new CategoryWillBeDeleted($category));

        $category->delete();
    }
}
