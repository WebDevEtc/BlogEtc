<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\BlogEtcCommentNotFoundException;
use WebDevEtc\BlogEtc\Models\Comment;

class CommentsRepository
{
    /**
     * @var Comment
     */
    private $model;

    /**
     * BlogEtcCommentsRepository constructor.
     *
     * @param Comment $model
     */
    public function __construct(Comment $model)
    {
        $this->model = $model;
    }

    /**
     * Return new instance of the Query Builder for this model.
     *
     * @param bool $eagerLoad
     *
     * @return Builder
     */
    public function query(bool $eagerLoad = false): Builder
    {
        $queryBuilder = $this->model->newQuery();

        if ($eagerLoad === true) {
            $queryBuilder->with('post');
        }

        return $queryBuilder;
    }

    /**
     * Find and return a comment by ID.
     *
     * If $onlyApproved is true, then it will only return an approved comment
     * If it is false then it can return it even if not yet approved
     *
     * @param int  $blogEtcCommentID
     * @param bool $onlyApproved
     *
     * @return Comment
     */
    public function find(int $blogEtcCommentID, bool $onlyApproved = true): Comment
    {
        try {
            $queryBuilder = $this->query(true);

            if (!$onlyApproved) {
                $queryBuilder->withoutGlobalScopes();
            }

            return $queryBuilder->findOrFail($blogEtcCommentID);
        } catch (ModelNotFoundException $e) {
            throw new BlogEtcCommentNotFoundException('Unable to find blog post comment with ID: '.$blogEtcCommentID);
        }
    }
}
