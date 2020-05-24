<?php

namespace WebDevEtc\BlogEtc\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WebDevEtc\BlogEtc\Exceptions\PostNotFoundException;
use WebDevEtc\BlogEtc\Models\Post;

/**
 * Class BlogEtcPostsRepository.
 */
class PostsRepository
{
    /**
     * @var Post
     */
    private $model;

    /**
     * BlogEtcPostsRepository constructor.
     */
    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    /**
     * Return blog posts ordered by posted_at, paginated.
     *
     * @param int $categoryID
     */
    public function indexPaginated(int $perPage = 10, int $categoryID = null): LengthAwarePaginator
    {
        $query = $this->query(true)
            ->orderBy('posted_at', 'desc');

        if ($categoryID) {
            $query->whereHas('categories', static function (Builder $query) use ($categoryID) {
                $query->where('blog_etc_post_categories.blog_etc_category_id', $categoryID);
            })->get();
        }

        return $query->paginate($perPage);
    }

    /**
     * Return new instance of the Query Builder for this model.
     */
    public function query(bool $eagerLoad = false): Builder
    {
        $queryBuilder = $this->model->newQuery();

        if (true === $eagerLoad) {
            $queryBuilder->with(['categories']);
        }

        return $queryBuilder;
    }

    /**
     * Return posts for RSS feed.
     *
     * @return Builder[]|Collection
     */
    public function rssItems(): Collection
    {
        return $this->query(false)
            ->orderBy('posted_at', 'desc')
            ->limit(config('blogetc.rssfeed.posts_to_show_in_rss_feed'))
            ->with('author')
            ->get();
    }

    /**
     * Find a blog etc post by ID
     * If cannot find, throw exception.
     */
    public function findBySlug(string $slug): Post
    {
        try {
            // the published_at + is_published are handled by BlogEtcPublishedScope, and don't take effect if the
            // logged in user can manage log posts
            return $this->query(true)
                ->where('slug', $slug)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new PostNotFoundException('Unable to find blog post with slug: '.$slug);
        }
    }

    /**
     * Create a new BlogEtcPost post.
     */
    public function create(array $attributes): Post
    {
        return $this->query()->create($attributes);
    }

    /**
     * Delete a post.
     *
     * @throws Exception
     */
    public function delete(int $postID): bool
    {
        $post = $this->find($postID);

        return $post->delete();
    }

    /**
     * Find a blog etc post by ID
     * If cannot find, throw exception.
     */
    public function find(int $blogEtcPostID): Post
    {
        try {
            return $this->query(true)->findOrFail($blogEtcPostID);
        } catch (ModelNotFoundException $e) {
            throw new PostNotFoundException('Unable to find blog post with ID: '.$blogEtcPostID);
        }
    }

    /**
     * Update image sizes (or in theory any attribute) on a blog etc post.
     *
     * TODO - currently untested.
     *
     * @param array $uploadedImages
     */
    public function updateImageSizes(Post $post, ?array $uploadedImages): Post
    {
        if ($uploadedImages && count($uploadedImages)) {
            // does not use update() here as it would require fillable for each field - and in theory someone
            // might want to add more image sizes.
            foreach ($uploadedImages as $size => $imageName) {
                $post->$size = $imageName;
            }
            $post->save();
        }

        return $post;
    }

    /**
     * Search for posts.
     *
     * This is a rough implementation - proper full text search has been removed in current version.
     */
    public function search(string $search, int $max = 25): Collection
    {
        $query = $this->query(true)->limit($max);

        trim($search)
            ? $query->where('title', 'like', '%'.$search)
            : $query->where('title', '');

        return $query->get();
    }
}
