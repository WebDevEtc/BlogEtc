<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Laravelium\Feed\Feed;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\FeedRequest;

/**
 * Class BlogEtcRssFeedController.php
 * All RSS feed viewing methods
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcRssFeedController extends Controller
{
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
        // RSS feed is cached. Admin/writer users might see different content, so use a different cache for different users.
        // This should not be a problem unless your site has many logged in users.
        $userOrGuest = Auth::check()
            ? Auth::user()->id
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
     * @param $feed
     */
    protected function makeFreshFeed(Feed $feed): void
    {
        $posts = BlogEtcPost::orderBy('posted_at', 'desc')
            ->limit(config('blogetc.rssfeed.posts_to_show_in_rss_feed', 10))
            ->with('author')
            ->get();

        $this->setupFeed($feed, $posts);

        /** @var BlogEtcPost $post */
        foreach ($posts as $post) {
            $feed->add($post->title,
                $post->author_string(),
                $post->url(),
                $post->posted_at,
                $post->short_description,
                $post->generate_introduction()
            );
        }
    }

    /**
     * Basic set up of the Feed object
     *
     * @param Feed $feed
     * @param $posts
     */
    protected function setupFeed(Feed $feed, $posts)
    {
        $feed->title = config('app.name') . ' Blog';
        $feed->description = config('blogetc.rssfeed.description', 'Our blog RSS feed');
        $feed->link = route('blogetc.index');
        $feed->setDateFormat('carbon');
        $feed->pubdate = isset($posts[0]) ? $posts[0]->posted_at : Carbon::now()->subYear(10);
        $feed->lang = config('blogetc.rssfeed.language', 'en');
        $feed->setShortening(config('blogetc.rssfeed.should_shorten_text', true)); // true or false
        $feed->setTextLimit(config('blogetc.rssfeed.text_limit', 100));
    }
}
