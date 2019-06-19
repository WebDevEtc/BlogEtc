<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\BlogEtcCommentNotFoundException;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;

class BlogEtcCommentsRepository
{
    /**
     * @var BlogEtcComment
     */
    private $model;

    public function __construct(BlogEtcComment $model)
    {
        $this->model = $model;
    }

    /**
     * Return new instance of the Query Builder for this model
     * @param bool $eagerLoad
     * @return Builder
     */
    public function query(bool $eagerLoad = false): Builder
    {
        $queryBuilder = $this->model->newQuery();

        if ($eagerLoad === true) {
            $queryBuilder->with(['post',]);
        }

        return $queryBuilder;
    }

    /**
     * Find and return a comment by ID
     *
     * @param int $blogEtcCommentID
     * @return BlogEtcComment
     */
    public function find(int $blogEtcCommentID): BlogEtcComment
    {
        try {
            return $this->query(true)->findOrFail($blogEtcCommentID);
        } catch (ModelNotFoundException $e) {
            throw new BlogEtcCommentNotFoundException('Unable to find blog post comment with ID: ' . $blogEtcCommentID);
        }
    }
}
