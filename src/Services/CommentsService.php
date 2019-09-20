<?php

namespace WebDevEtc\BlogEtc\Services;

use Exception;
use WebDevEtc\BlogEtc\Events\CommentAdded;
use WebDevEtc\BlogEtc\Events\CommentApproved;
use WebDevEtc\BlogEtc\Events\CommentWillBeDeleted;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Repositories\CommentsRepository;

/**
 * Class BlogEtcCategoriesService.
 *
 * Service class to handle most logic relating to BlogEtcCategory entries.
 *
 * Some Eloquent/DB things are in here - but query heavy method belong in the repository, accessible
 * as $this->repository
 */
class CommentsService
{
    // comment system types. Set these in config file
    public const COMMENT_TYPE_BUILT_IN = 'built_in';
    public const COMMENT_TYPE_DISQUS = 'disqus';
    public const COMMENT_TYPE_CUSTOM = 'custom';
    public const COMMENT_TYPE_DISABLED = 'disabled';

    /** @var CommentsRepository */
    private $repository;

    public function __construct(CommentsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * BlogEtcCategoriesRepository repository - for query heavy method.
     *
     * I don't stick 100% to all queries belonging in the repo - some Eloquent
     * things are fine to have in the service where it makes sense.
     */
    public function repository(): CommentsRepository
    {
        return $this->repository;
    }

    public function create(
        Post $blogEtcPost,
        array $attributes,
        string $ip = null,
        int $userID = null
    ): Comment {
        // TODO - inject the model object, put into repo, generate $attributes
        // fill it with fillable attributes
        $newComment = new Comment($attributes);

        // then some additional attributes
        if (config('blogetc.comments.save_ip_address')) {
            $newComment->ip = $ip;
        }
        if (config('blogetc.comments.ask_for_author_website')) {
            $newComment->author_website = $attributes['author_website'] ?? '';
        }
        if (config('blogetc.comments.ask_for_author_website')) {
            $newComment->author_email = $attributes['author_email'] ?? '';
        }
        if (config('blogetc.comments.save_user_id_if_logged_in')) {
            $newComment->user_id = $userID;
        }

        // are comments auto approved?
        $newComment->approved = $this->autoApproved();

        $blogEtcPost->comments()->save($newComment);

        event(new CommentAdded($blogEtcPost, $newComment));

        return $newComment;
    }

    /**
     * Are comments auto approved?
     *
     * @return bool
     */
    protected function autoApproved(): bool
    {
        return config('blogetc.comments.auto_approve_comments', true) === true;
    }

    /**
     * Find and return a comment by ID.
     *
     * @param int  $blogEtcCommentID
     * @param bool $onlyApproved
     *
     * @return Comment
     */
    public function find(int $blogEtcCommentID, bool $onlyApproved = true): Comment
    {
        return $this->repository->find($blogEtcCommentID, $onlyApproved);
    }

    /**
     * Approve a blog comment.
     *
     * @param int $blogCommentID
     *
     * @return Comment
     */
    public function approve(int $blogCommentID): Comment
    {
        // get comment
        $comment = $this->find($blogCommentID, false);

        // mark as approved
        $comment->approved = true;

        // save changes
        $comment->save();

        // fire event
        event(new CommentApproved($comment));

        // return comment
        return $comment;
    }

    /**
     * Delete a blog comment.
     *
     * Returns the now deleted comment object
     *
     * @param int $blogCommentID
     *
     * @throws Exception
     *
     * @return Comment
     */
    public function delete(int $blogCommentID): Comment
    {
        // find the comment
        $comment = $this->find($blogCommentID, false);

        // fire event
        event(new CommentWillBeDeleted($comment));

        // delete it
        $comment->delete();

        // return deleted comment
        return $comment;
    }
}
