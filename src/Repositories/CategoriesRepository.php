<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\CategoryNotFoundException;
use WebDevEtc\BlogEtc\Models\Category;

/**
 * Class BlogEtcCategoriesRepository.
 */
class CategoriesRepository
{
    /** @var Category */
    private $model;

    /**
     * BlogEtcCategoriesRepository constructor.
     */
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * Return new instance of the Query Builder for this model.
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Return all blog etc categories, ordered by category_name, paginated.
     */
    public function indexPaginated(int $perPage = 25): LengthAwarePaginator
    {
        return $this->query()
            ->orderBy('category_name')
            ->paginate($perPage);
    }

    /**
     * Find and return a blog etc category.
     */
    public function find(int $categoryID): Category
    {
        try {
            return $this->query()->findOrFail($categoryID);
        } catch (ModelNotFoundException $e) {
            throw new CategoryNotFoundException('Unable to find a blog category with ID: '.$categoryID);
        }
    }

    /**
     * Find and return a blog etc category, based on its slug.
     */
    public function findBySlug(string $categorySlug): Category
    {
        try {
            return $this->query()->where('slug', $categorySlug)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new CategoryNotFoundException('Unable to find a blog category with slug: '.$categorySlug);
        }
    }
}
