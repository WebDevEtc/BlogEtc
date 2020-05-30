<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Gates\GateTypes;
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
     * Test the index page loads.
     *
     * This is a basic test that just checks the correct view is loaded, correct status is returned.
     */
    public function testIndex(): void
    {
        $response = $this->get(route('blogetc.index'));

        $response->assertOk()->assertViewHas('posts');
    }

    public function testIndexAsLoggedInLegacyNonAdmin(): void
    {
        $this->beLegacyNonAdminUser();

        $response = $this->get(route('blogetc.index'));

        $response->assertOk()->assertViewHas('posts');
    }

    public function testIndexAsLoggedInGatedNonAdmin(): void
    {
        $this->beNonAdminUserWithGate();

        $response = $this->get(route('blogetc.index'));

        $response->assertOk()->assertViewHas('posts');
    }

    public function testIndexAsLoggedInLegacyAdmin(): void
    {
        $this->beLegacyAdminUser();

        $response = $this->get(route('blogetc.index'));

        $response->assertOk()->assertViewHas('posts');
    }

    public function testIndexAsLoggedInGatedAdmin(): void
    {
        $this->beAdminUserWithGate();

        $response = $this->get(route('blogetc.index'));

        $response->assertOk()->assertViewHas('posts');
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

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertOk()
            ->assertViewHas('post', $post)
            ->assertSee($post->title)
            ->assertSee($post->description);
    }

    /**
     * Test that an invalid slug returns a 404 response.
     */
    public function testShow404(): void
    {
//        $response = $this->get(route('blogetc.show', 'invalid-id'));
        $response = $this->get(route('blogetc.single', 'invalid-id'));

        $response->assertNotFound();
    }

    /**
     * A post with is_published = false should not be shown.
     */
    public function testShow404IfNotPublished(): void
    {
        $post = factory(Post::class)->state('not_published')->create();

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertNotFound();
    }

    /**
     * A post with is_published = false should not be shown.
     */
    public function testShow404IfNotPublishedWithGates(): void
    {
        $this->beNonAdminUserWithGate();
        $post = factory(Post::class)->state('not_published')->create();

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertNotFound();
    }
    /**
     * A post with is_published = false should not be shown.
     */
    public function testShow404IfNotPublishedWithLegacy(): void
    {
        $this->beLegacyNonAdminUser();
        $post = factory(Post::class)->state('not_published')->create();

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertNotFound();
    }

    /**
     * A post with is_published = false should be visible if logged in with user which passes the gate check.
     */
    public function testShowAdminCanSeeNotPublished(): void
    {
        $this->beLegacyAdminUser();
        $post = factory(Post::class)->state('not_published')->create();

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertOk()->assertViewHas('post', $post);
    }

    /**
     * A post with posted_at in the future should not be shown.
     */
    public function testShow404IfFuturePost(): void
    {
        $post = factory(Post::class)->state('in_future')->create();

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertNotFound();
    }

    /**
     * A post with posted_at in the future should not be shown.
     */
    public function testAdminsCanSeeFuturePosts(): void
    {
        $this->beLegacyAdminUser();

        $post = factory(Post::class)->state('in_future')->create();

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertOk()->assertViewHas('post', $post);
    }

    /**
     * A post which was deleted should show a 404.
     */
    public function testShow404IfDeletedPost(): void
    {
        $post = factory(Post::class)->create();
        $this->beLegacyAdminUser();

        $post->delete();

        $response = $this->get(route('blogetc.single', $post->slug));

        $response->assertNotFound();
    }

    /**
     * Test the category route.
     */
    public function testCategory(): void
    {
        Post::truncate();
        Category::truncate();

        $post = factory(Post::class)->create();
        $category = factory(Category::class)->create();
        $post->categories()->save($category);

        $response = $this->get(route('blogetc.view_category', $category->slug));

        $response->assertOk()
            ->assertViewHas('blogetc_category', $category)
            ->assertSee($post->title);
    }

    /**
     * Test the category route.
     */
    public function testCategoryExcludesOtherCategories(): void
    {
        $post = factory(Post::class)->create();
        $category = factory(Category::class)->create();
        $post->categories()->save($category);
        $unrelatedCategory = factory(Category::class)->create();

        $response = $this->get(route('blogetc.view_category', $unrelatedCategory->slug));

        $response->assertOk();
        $response->assertViewHas('blogetc_category', $unrelatedCategory);
        $response->assertDontSee($post->title);
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
