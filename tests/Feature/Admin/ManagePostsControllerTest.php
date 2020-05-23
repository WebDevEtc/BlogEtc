<?php

namespace WebDevEtc\BlogEtc\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\RedirectResponse;
//use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Models\Category;
//use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Tests\TestCase;

class ManagePostsControllerTest extends TestCase
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
     * Test that users passing the admin gate can access the admin index.
     *
     * These authentication tests are only done on the single index route as they should all be caught by
     * the 'can:blog-etc-admin' middleware on the admin group.
     *
     * (Via gate)
     */
    public function testAdminUsersCanAccess(): void
    {
        $this->beAdminUser();

        $response = $this->get(route('blogetc.admin.index'));

        $response->assertOk();
    }

    /**
     * Assert that the index admin page is not accessible for guests.
     * (Via gate).
     */
    public function testForbiddenToNonAdminUsers(): void
    {
        $this->beNonAdminUser();

        $response = $this->get(route('blogetc.admin.index'));

        $this->assertSame(RedirectResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Assert that the index admin page is not accessible for guests.
     * (Via auth middleware).
     */
    public function testForbiddenToGuests(): void
    {
        $response = $this->get(route('blogetc.admin.index'));

        $this->assertSame(RedirectResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Test admin index page lists posts.
     */
    public function testIndexIncludesRecentPost(): void
    {
        $post = factory(Post::class)->create();

        $this->beAdminUser();

        $response = $this->get(route('blogetc.admin.index'));

        $response->assertSee($post->title);
        $response->assertViewHas('posts');
        $response->viewData('posts');
    }

    /**
     * Test the create form is displayed.
     */
    public function testCreateForm(): void
    {
        $this->beAdminUser();
        $response = $this->get(route('blogetc.admin.create_post'));
        $response->assertOk();
    }

    /**
     * Test that new blog posts can be stored.
     */
    public function testStore(): void
    {
        $this->beAdminUser();

        $params = [
            'posted_at'         => null,
            'title'             => $this->faker->sentence,
            'subtitle'          => null,
            'post_body'         => $this->faker->paragraph,
            'meta_desc'         => $this->faker->paragraph,
            'short_description' => $this->faker->paragraph,
        ];

        $response = $this->post(route('blogetc.admin.store_post'), $params);

        $response->assertRedirect()->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('blog_etc_posts', ['title' => $params['title']]);
    }

    /**
     * Test that new posts can be created, and associated to a category.
     */
    public function testStoreWithCategory(): void
    {
        $this->beAdminUser();

        $category = factory(Category::class)->create();

        $params = [
            'title'             => $this->faker->sentence,
            'post_body'         => $this->faker->paragraph,
            'meta_desc'         => $this->faker->paragraph,
            'short_description' => $this->faker->paragraph,
            'category'          => [$category->id => '1'],
        ];

        $response = $this->post(route('blogetc.admin.store_post'), $params);

        $response->assertRedirect()->assertSessionDoesntHaveErrors();

        $redirectTo = $response->headers->get('location');
        $postId = array_last(explode('/', $redirectTo));

        $this->assertDatabaseHas('blog_etc_post_categories', ['blog_etc_post_id' => $postId, 'blog_etc_category_id' => $category->id]);
    }

    /**
     * Test that new posts can be created, and associated to a category.
     */
    public function testStoreWithInvalidCategory(): void
    {
        $this->beAdminUser();
        $invalidCategoryID = 99999;

        $params = [
            'title'             => $this->faker->sentence,
            'post_body'         => $this->faker->paragraph,
            'meta_desc'         => $this->faker->paragraph,
            'short_description' => $this->faker->paragraph,
            'category'          => [$invalidCategoryID => '1'],
        ];

        $response = $this->post(route('blogetc.admin.store_post'), $params);

        // TODO - there should be a request fobidding access if trying to store invalid category id

        $post = Post::where('title', $params['title'])->where('post_body', $params['post_body'])->firstOrFail();

        $this->assertDatabaseMissing('blog_etc_post_categories', ['blog_etc_post_id' => $post->id]);
    }

    /**
     * Test the edit form.
     */
    public function testEdit(): void
    {
        $this->beAdminUser();

        $post = factory(Post::class)->create();

        $response = $this->get(route('blogetc.admin.edit_post', $post));

        $response->assertOk()->assertSee($post->title);
    }

    /**
     * Test that trying to edit an invalid blog post does not work.
     */
    public function testEditInvalidPost(): void
    {
        $this->beAdminUser();

        $invalidID = 9999;

        $response = $this->get(route('blogetc.admin.edit_post', $invalidID));

        $response->assertNotFound();
    }

    /**
     * Test can delete a post.
     */
    public function testDestroy(): void
    {
        $this->beAdminUser();

        $post = factory(Post::class)->create();

        $response = $this->delete(route('blogetc.admin.destroy_post', $post));

        $response->assertOk()->assertViewIs('blogetc_admin::posts.deleted_post');

        $this->assertDatabaseMissing('blog_etc_posts', ['id' => $post->id]);
    }

    /**
     * Test 404 response when deleting invalid post ID.
     */
    public function testDestroyInvalidPostID(): void
    {
        $this->beAdminUser();

        $invalidPostID = 999;
        $response = $this->delete(route('blogetc.admin.destroy_post', $invalidPostID));

        $response->assertNotFound();
    }

    /**
     * Test can update a post.
     */
    public function testUpdate(): void
    {
        $this->beAdminUser();

        $post = factory(Post::class)->create();

        $params = $post->toArray();

        // Update title.
        $params['title'] = $this->faker->sentence;

        $response = $this->patch(route('blogetc.admin.edit_post', $post), $params);

        $response->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('blog_etc_posts', ['title' => $params['title']]);
    }

    /**
     * Test trying to update a post which does not exist gives a 404 response.
     */
    public function testUpdateInvalidPostID(): void
    {
        $invalidPostID = 10000;
        $this->beAdminUser();

        $params = factory(Post::class)->make()->toArray();

        $response = $this->patch(route('blogetc.admin.edit_post', $invalidPostID), $params);

        $response->assertNotFound();
    }
}
