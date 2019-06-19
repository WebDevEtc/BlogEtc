<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\BlogEtcPostNotFoundException;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

class BlogEtcPostsRepository
{
    /**
     * @var BlogEtcPost
     */
    private $model;

    public function __construct(BlogEtcPost $model)
    {
        $this->model = $model;
    }

    /**
     * Return blog posts ordered by posted_at, paginated
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function indexPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query(true)
            ->orderBy('posted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Return new instance of the Query Builder for this model
     * @return Builder
     */
    public function query(bool $eagerLoad = false): Builder
    {
        $queryBuilder = $this->model->newQuery();

        if ($eagerLoad === true) {
            // eager load the categories relationship.
            // Comments probably don't need to be loaded for most queries.
            $queryBuilder->with(['categories',]);
        }

        return $queryBuilder;
    }

    /**
     * Find a blog etc post by ID
     * If cannot find, throw exception
     *
     * @param int $blogEtcPostID
     * @return BlogEtcPost
     */
    public function find(int $blogEtcPostID): BlogEtcPost
    {
        try {
            return $this->query(true)->findOrFail($blogEtcPostID);
        } catch (ModelNotFoundException $e) {
            throw new BlogEtcPostNotFoundException('Unable to find blog post with ID: ' . $blogEtcPostID);
        }
    }

    /**
     * Find a blog etc post by ID
     * If cannot find, throw exception
     *
     * @param string $slug
     * @return BlogEtcPost
     */
    public function findBySlug(string $slug): BlogEtcPost
    {
        try {
            return $this->query(true)->where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new BlogEtcPostNotFoundException('Unable to find blog post with slug: ' . $slug);
        }
    }

}
