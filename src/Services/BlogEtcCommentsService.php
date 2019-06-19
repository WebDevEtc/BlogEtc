<?php

namespace WebDevEtc\BlogEtc\Services;

use Illuminate\Database\Eloquent\Collection;
use WebDevEtc\BlogEtc\Events\CommentAdded;
use WebDevEtc\BlogEtc\Models\BlogEtcComment;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Repositories\BlogEtcCommentsRepository;

/**
 * Class BlogEtcCategoriesService
 *
 * Service class to handle most logic relating to BlogEtcCategory entries.
 *
 * Some Eloquent/DB things are in here - but query heavy method belong in the repository, accessible
 * as $this->repository, or publicly via repository()
 *
 * @package WebDevEtc\BlogEtc\Services
 */
class BlogEtcCommentsService
{
    /**
     * @var BlogEtcCommentsRepository
     */
    private $repository;

    public function __construct(BlogEtcCommentsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * BlogEtcCategoriesRepository repository - for query heavy method.
     *
     * I don't stick 100% to all queries belonging in the repo - some Eloquent
     * things are fine to have in the service where it makes sense.
     *
     */
    public function repository(): BlogEtcCommentsRepository
    {
        return $this->repository;
    }

    /**
     * @param bool $includeUnapproved
     * @return Collection
     */
    public function all($includeUnapproved = false): Collection
    {

    }

//
//    /**
//     * Create a new BlogEtcCategory entry
//     *
//     * @param array $attributes
//     * @return BlogEtcCategory
//     */
//    public function create(array $attributes): BlogEtcCategory
//    {
//        $new_category = new BlogEtcCategory($attributes);
//        $new_category->save();
//
//
//        event(new CategoryAdded($new_category));
//    }
//
//    /**
//     * Update a blog etc category entry
//     *
//     * @param int $categoryID
//     * @param array $attributes
//     * @return BlogEtcCategory
//     */
//    public function update(int $categoryID, array $attributes):BlogEtcCategory
//    {
//        /** @var BlogEtcCategory $category */
//        $category = BlogEtcCategory::findOrFail($categoryID);
//        $category->fill($attributes);
//        $category->save();
//
//        event(new CategoryEdited($category));
//
//        return $category;
//    }
//
//    /**
//     * Delete a BlogEtcCategory
//     *
//     * @param int $categoryID
//     */
//    public function delete(int $categoryID):void
//    {
//        $category = BlogEtcCategory::findOrFail($categoryID);
//        event(new CategoryWillBeDeleted($category));
//        $category->delete();
//    }

    public function create(
        BlogEtcPost $blogEtcPost,
        array $attributes,
        string $ip = null,
        int $userID = null
    ): BlogEtcComment {
        // TODO - inject the model object, put into repo, generate $attributes
        // fill it with fillable attributes
        $newComment = new BlogEtcComment($attributes);

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
     * @return bool
     */
    protected function autoApproved(): bool
    {
        return config('blogetc.comments.auto_approve_comments', true) === true;
    }


//    public function create()
//    {
//
//
//
//
//
//        $new_comment = $this->createNewComment($request, $blogPost);
//
//}

}
