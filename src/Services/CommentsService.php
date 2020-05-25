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
 * Service class to handle most logic relating to Comment entries.
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

    /**
     * CommentsService constructor.
     */
    public function __construct(CommentsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * BlogEtcCategoriesRepository repository - for query heavy method.
     */
    public function repository(): CommentsRepository
    {
        return $this->repository;
    }

    /**
     * Create a new comment.
     */
    public function create(
        Post $blogEtcPost,
        array $attributes,
        string $ip = null,
        int $userID = null
    ): Comment {
        $ip = config('blogetc.comments.save_ip_address')
            ? $ip : null;

        $authorWebsite = config('blogetc.comments.ask_for_author_website') && ! empty($attributes['author_website'])
            ? $attributes['author_website']
            : null;

        $authorEmail = config('blogetc.comments.ask_for_author_website') && ! empty($attributes['author_email'])
            ? $attributes['author_email']
            : null;

        $userID = config('blogetc.comments.save_user_id_if_logged_in')
            ? $userID
            : null;

        $approved = $this->autoApproved();

        $newComment = $this->repository->create(
            $blogEtcPost,
            $attributes,
            $ip,
            $authorWebsite,
            $authorEmail,
            $userID,
            $approved
        );

        event(new CommentAdded($blogEtcPost, $newComment));

        return $newComment;
    }

    /**
     * Are comments auto approved?
     */
    protected function autoApproved(): bool
    {
        return true === config('blogetc.comments.auto_approve_comments', true);
    }

    /**
     * Approve a blog comment.
     */
    public function approve(int $blogCommentID): Comment
    {
        $comment = $this->repository->approve($blogCommentID);
        event(new CommentApproved($comment));

        return $comment;
    }

    /**
     * Delete a blog comment.
     *
     * Returns the now deleted comment object
     *
     * @throws Exception
     */
    public function delete(int $blogCommentID): Comment
    {
        $comment = $this->find($blogCommentID, false);
        event(new CommentWillBeDeleted($comment));
        $comment->delete();

        return $comment;
    }

    /**
     * Find and return a comment by ID.
     */
    public function find(int $blogEtcCommentID, bool $onlyApproved = true): Comment
    {
        return $this->repository->find($blogEtcCommentID, $onlyApproved);
    }
}
