<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Laravelium\Feed\Feed;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Requests\FeedRequest;

/**
 * Class BlogEtcRssFeedController.php
 * All RSS feed viewing methods.
 */
class BlogEtcRssFeedController extends Controller
{
    /**
     * RSS Feed
     * This is a long (but quite simple) method to show an RSS feed
     * It makes use of Laravelium\Feed\Feed.
     *
     * @return mixed
     */
    public function feed(FeedRequest $request, Feed $feed)
    {
        // for different caching
        $user_or_guest = Auth::check() ? Auth::user()->id : 'guest';

        $feed->setCache(
            config('blogetc.rssfeed.cache_in_minutes', 60),
            'blogetc-'.$request->getFeedType().$user_or_guest
        );

        if (!$feed->isCached()) {
            $this->makeFreshFeed($feed);
        }

        return $feed->render($request->getFeedType());
    }

    /**
     * @param $feed
     */
    protected function makeFreshFeed(Feed $feed)
    {
        $posts = Post::orderBy('posted_at', 'desc')
            ->limit(config('blogetc.rssfeed.posts_to_show_in_rss_feed', 10))
            ->with('author')
            ->get();

        $this->setupFeed($feed, $posts);

        /** @var Post $post */
        foreach ($posts as $post) {
            $feed->add($post->title,
                $post->authorString(),
                $post->url(),
                $post->posted_at,
                $post->short_description,
                $post->generateIntroduction()
            );
        }
    }

    /**
     * @param $posts
     *
     * @return mixed
     */
    protected function setupFeed(Feed $feed, $posts)
    {
        $feed->title = config('app.name').' Blog';
        $feed->description = config('blogetc.rssfeed.description', 'Our blog RSS feed');
        $feed->link = route('blogetc.index');
        $feed->setDateFormat('carbon');
        $feed->pubdate = isset($posts[0]) ? $posts[0]->posted_at : Carbon::now()->subYear(10);
        $feed->lang = config('blogetc.rssfeed.language', 'en');
        $feed->setShortening(config('blogetc.rssfeed.should_shorten_text', true));
        $feed->setTextLimit(config('blogetc.rssfeed.text_limit', 100));
    }
}
