<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\BlogEtcCategoryNotFoundException;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;

class BlogEtcCategoriesRepository
{
    /**
     * @var BlogEtcCategory
     */
    private $model;

    public function __construct(BlogEtcCategory $model)
    {
        $this->model = $model;
    }

    /**
     * Return new instance of the Query Builder for this model
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Return all blog etc categories, ordered by category_name, paginated
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function indexPaginated(int $perPage = 25): LengthAwarePaginator
    {
        return $this->query(true)
            ->orderBy('category_name')
            ->paginate($perPage);
    }

    /**
     * Find and return a blog etc category
     *
     * @param int $categoryID
     * @return BlogEtcCategory
     */
    public function find(int $categoryID): BlogEtcCategory
    {
        try {
            return $this->query()->findOrFail($categoryID);
        } catch (ModelNotFoundException $e) {
            throw new BlogEtcCategoryNotFoundException('Unable to find a blog category with ID: ' . $categoryID);
        }
    }

}
