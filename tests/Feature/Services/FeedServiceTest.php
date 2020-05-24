<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Laravelium\Feed\Feed;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Services\FeedService;
use WebDevEtc\BlogEtc\Tests\TestCase;

class FeedServiceTest extends TestCase
{
    use WithFaker;

    /** @var FeedService */
    protected $feedService;

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
        Post::truncate();

        $this->feedService = resolve(FeedService::class);
    }

    public function testGetFeed()
    {
        $feed = resolve(Feed::class);

        factory(Post::class)->create();

        $response = $this->feedService->getFeed($feed, 'rss');

        $this->assertInstanceOf(Response::class, $response);
    }

    // Todo: test content, test logged in vs logged out, test cache, test empty posts
}
