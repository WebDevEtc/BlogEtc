<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\CommentNotFoundException;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;

class CommentsRepository
{
    /**
     * @var Comment
     */
    private $model;

    /**
     * BlogEtcCommentsRepository constructor.
     */
    public function __construct(Comment $model)
    {
        $this->model = $model;
    }

    /**
     * Approve a blog comment.
     */
    public function approve(int $blogCommentID): Comment
    {
        $comment = $this->find($blogCommentID, false);
        $comment->approved = true;
        $comment->save();

        return $comment;
    }

    /**
     * Find and return a comment by ID.
     *
     * If $onlyApproved is true, then it will only return an approved comment
     * If it is false then it can return it even if not yet approved
     */
    public function find(int $blogEtcCommentID, bool $onlyApproved = true): Comment
    {
        try {
            $queryBuilder = $this->query(true);

            if (! $onlyApproved) {
                $queryBuilder->withoutGlobalScopes();
            }

            return $queryBuilder->findOrFail($blogEtcCommentID);
        } catch (ModelNotFoundException $e) {
            throw new CommentNotFoundException('Unable to find blog post comment with ID: '.$blogEtcCommentID);
        }
    }

    /**
     * Return new instance of the Query Builder for this model.
     */
    public function query(bool $eagerLoad = false): Builder
    {
        $queryBuilder = $this->model->newQuery();

        if (true === $eagerLoad) {
            $queryBuilder->with('post');
        }

        return $queryBuilder;
    }

    /**
     * Create a comment.
     */
    public function create(
        Post $post,
        array $attributes,
        string $ip = null,
        string $authorWebsite = null,
        string $authorEmail = null,
        int $userID = null,
        bool $autoApproved = false
    ): Comment {
        // TODO - inject the model object, put into repo, generate $attributes
        $newComment = new Comment($attributes);

        $newComment->ip = $ip;
        $newComment->author_website = $authorWebsite;
        $newComment->author_email = $authorEmail;
        $newComment->user_id = $userID;
        $newComment->approved = $autoApproved;

        $post->comments()->save($newComment);

        return $newComment;
    }
}
