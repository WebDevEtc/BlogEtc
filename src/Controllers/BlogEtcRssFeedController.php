<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Laravelium\Feed\Feed;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\FeedRequest;
use WebDevEtc\BlogEtc\Services\BlogEtcPostsService;

/**
 * Class BlogEtcRssFeedController.php
 * All RSS feed viewing methods.
 *
 * A lot of this belongs in a service - for now it lives in this file...
 *
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcRssFeedController extends Controller
{
    /** @var BlogEtcPostsService */
    private $postsService;

    public function __construct(BlogEtcPostsService $postsService)
    {
        $this->postsService = $postsService;
    }
    /**
     * RSS Feed
     * This is a long (but quite simple) method to show an RSS feed
     * It makes use of Laravelium\Feed\Feed.
     *
     * @param FeedRequest $request
     * @param Feed $feed
     * @return mixed
     */
    public function feed(FeedRequest $request, Feed $feed)
    {
        // RSS feed is cached. Admin/writer users might see different content, so
        // use a different cache for different users.

        // This should not be a problem unless your site has many logged in users.
        $userOrGuest = Auth::check()
            ? Auth::id()
            : 'guest';

        $feed->setCache(
            config('blogetc.rssfeed.cache_in_minutes', 60),
            'blogetc-' . $request->getFeedType() . $userOrGuest
        );

        if (!$feed->isCached()) {
            $this->makeFreshFeed($feed);
        }

        return $feed->render($request->getFeedType());
    }

    /**
     * Create fresh feed by passing latest blog posts
     *
     * @param $feed
     */
    protected function makeFreshFeed(Feed $feed): void
    {
        $blogPosts = $this->postsService->rssItems();

        $this->setupFeed($feed, $blogPosts);

        /** @var BlogEtcPost $blogPost */
        foreach ($blogPosts as $blogPost) {
            $feed->add(
                $blogPost->title,
                $blogPost->authorString(),
                $blogPost->url(),
                $blogPost->posted_at,
                $blogPost->short_description,
                $blogPost->generateIntroduction()
            );
        }
    }

    /**
     * Basic set up of the Feed object
     *
     * @param Feed $feed
     * @param BlogEtcPostsService[]|Collection $posts
     * @return Feed
     */
    protected function setupFeed(Feed $feed, Collection $posts): Feed
    {
        $feed->title = config('blogetc.rssfeed.title');
        $feed->description = config('blogetc.rssfeed.description');
        $feed->link = route('blogetc.index');
        $feed->lang = config('blogetc.rssfeed.language');
        $feed->setShortening(config('blogetc.rssfeed.should_shorten_text'));
        $feed->setTextLimit(config('blogetc.rssfeed.text_limit'));
        $feed->setDateFormat('carbon');
        $feed->pubdate = $posts->first()
            ? $posts->first()->posted_at // use first post if we have any...
            : Carbon::now(); // ... or default to now

        return $feed;
    }
}
