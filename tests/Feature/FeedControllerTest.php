<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Auth;
use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Tests\TestCase;

class FeedControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Test the main RSS feed.
     */
    public function testIndex(): void
    {
        $response = $this->get(route('blogetc.feed'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/atom+xml; charset=utf-8');
    }

    /**
     * Test the feed includes a recent post.
     */
    public function testIncludesRecentPost(): void
    {
        $post = factory(Post::class)->create();

        $response = $this->get(route('blogetc.feed'));

        $response->assertOk()
            ->assertSee($post->title);
    }

    /**
     * Test the feed does not include posts not published.
     */
    public function testExcludesUnpublishedPosts(): void
    {
        $post = factory(Post::class)->state('not_published')->create();

        $response = $this->get(route('blogetc.feed'));

        $response->assertOk()
            ->assertDontSee($post->title);
    }

    /**
     * Test the feed does not include posts not published.
     */
    public function testExcludesFuturePosts(): void
    {
        $post = factory(Post::class)->state('in_future')->create();

        $response = $this->get(route('blogetc.feed'));

        $response->assertOk()
            ->assertDontSee($post->title);
    }

    /**
     * Test works for logged in users.
     */
    public function testLoggedIn(): void
    {
        $this->beLegacyNonAdminUser();

        $response = $this->get(route('blogetc.feed'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/atom+xml; charset=utf-8');
    }

    /**
     * Test that logged in users which pass the blog-etc-admin gate can see unpublished posts.
     */
    public function testLoggedInCanSeeUnpublishedPosts(): void
    {
        $this->beLegacyAdminUser();

        $post = factory(Post::class)->state('not_published')->create();

        $response = $this->get(route('blogetc.feed'));

        $response->assertOk()->assertSee($post->title);
    }

    /**
     * Test that logged in users which pass the blog-etc-admin gate can see unpublished posts.
     */
    public function testLoggedInCanSeeFuturePosts(): void
    {
        $this->beLegacyAdminUser();

        $post = factory(Post::class)->state('in_future')->create();

        $response = $this->get(route('blogetc.feed'));

        $response->assertOk()
            ->assertSee($post->title);
    }

    /**
     * RSS is cached.
     * If viewing it with an admin user then a guest user, the guest user should not see the cached admin results.
     */
    public function testLoggedInCacheDoesNotShowToNonLoggedInUsers(): void
    {
        $this->beLegacyAdminUser();

        $post = factory(Post::class)->state('not_published')->create();

        $adminResponse = $this->get(route('blogetc.feed'));

        $adminResponse->assertOk()
            ->assertSee($post->title);

        Auth::logout();

        $guestResponse = $this->get(route('blogetc.feed'));
        $guestResponse->assertOk()
            ->assertDontSee($post->title);
    }

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
    }
}
