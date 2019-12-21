<?php

namespace WebDevEtc\BlogEtc\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Tests\TestCase;

/**
 * Class PostsControllerTest.
 *
 * Test the posts controller.
 */
class PostsControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
    }

    /**
     * Test the index page loads.
     *
     * This is a basic test that just checks the correct view is loaded, correct status is returned.
     */
    public function testIndex(): void
    {
        $url = route('blogetc.index');

        $this->withoutExceptionHandling();

        $this->mockView('blogetc::index', [Mockery::type('array'), Mockery::type('array')]);
        // also see assertions made in the mocked view.
        $response = $this->get($url);

        $response->assertOk();
    }

    /**
     * Test the show page loads.
     *
     * It is a bit awkward to test this as a package.
     * This will get refactored into a neater test.
     */
    public function testShow(): void
    {
        $this->withoutExceptionHandling();
        $slug = $this->faker->lexify('?????????');
        $post = new Post(['title' => $slug, 'is_published' => true, 'posted_at' => Carbon::now()]);
        $post->save();

        $url = route('blogetc.show', $post->slug);

        // As this package does not include layouts.app, it is easier to just mock the whole View part, and concentrate
        // only on the package code in the controller. Would be interested if anyone has a suggestion on better way
        // to test this.
        $this->mockView('blogetc::single_post', [Mockery::type('array'), Mockery::type('array')]);

        // also see assertions made in the mocked view.
        $response = $this->get($url);

        $response->assertOk();
    }
}
