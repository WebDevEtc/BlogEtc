<?php

namespace WebDevEtc\BlogEtc\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Models\Category;
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
        $response = $this->get(route('blogetc.index'));

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
        $post = factory(Post::class)->create();

        $response = $this->get(route('blogetc.show', $post->slug));

        $response->assertOk();
    }

    /**
     * Test that an invalid slug returns a 404 response.
     */
    public function testShow404(): void
    {
        $response = $this->get(route('blogetc.show', 'invalid-id'));

        $response->assertNotFound();
    }

    /**
     * A post with is_published = false should not be shown.
     */
    public function testShow404IfNotPublished(): void
    {
        $post = factory(Post::class)->state('not_published')->create();

        $response = $this->get(route('blogetc.show', $post->slug));

        $response->assertNotFound();
    }

    /**
     * A post with posted_at in the future should not be shown.
     */
    public function testShow404IfFuturePost(): void
    {
        $post = factory(Post::class)->state('in_future')->create();

        $response = $this->get(route('blogetc.show', $post->slug));

        $response->assertNotFound();
    }

    /**
     * A post which was deleted should show a 404.
     */
    public function testShow404IfDeletedPost(): void
    {
        $post = factory(Post::class)->create();
        $post->delete();

        $response = $this->get(route('blogetc.show', $post->slug));

        $response->assertNotFound();
    }

    /**
     * Test the category route.
     */
    public function testCategory(): void
    {
        $this->withoutExceptionHandling();
        $post = factory(Post::class)->create();
        $category = factory(Category::class)->create();
        $post->categories()->save($post);

        $response = $this->get(route('blogetc.view_category', $category->slug));

        $response->assertOk();
        $response->assertViewHas('category', $category);
    }
}
