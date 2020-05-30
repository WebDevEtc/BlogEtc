<?php

namespace WebDevEtc\BlogEtc\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\RedirectResponse;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Tests\TestCase;

class ManageCategoriesControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Test that users passing the admin gate can access the admin index.
     *
     * These authentication tests are only done on the single index route as they should all be caught by
     * the 'can:blog-etc-admin' middleware on the admin group.
     *
     * (Via legacy (non gate))
     */
    public function testLegacyAdminUsersCanAccess(): void
    {
        $this->beLegacyAdminUser();

        $response = $this->get(route('blogetc.admin.categories.index'));

        $response->assertOk();
    }

    /**
     * Test access to admin panel (via gates).
     */
    public function testGatedAdminUsersCanAccess(): void
    {
        $this->beAdminUserWithGate();

        $response = $this->get(route('blogetc.admin.categories.index'));

        $response->assertOk();
    }

    /**
     * Assert that the index admin page is not accessible for guests.
     * (Via legacy (non gate)).
     */
    public function testLegacyForbiddenToNonAdminUsersDefaultGate(): void
    {
        $this->beLegacyNonAdminUser();

        $response = $this->get(route('blogetc.admin.categories.index'));

        $this->assertSame(RedirectResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Assert that the index admin page is not accessible for guests.
     * (Via gate).
     */
    public function testGatedForbiddenToNonAdminUsers(): void
    {
        $this->beNonAdminUserWithGate();

        $response = $this->get(route('blogetc.admin.categories.index'));

        $this->assertSame(RedirectResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Assert that the index admin page is not accessible for guests.
     * (Via auth middleware).
     */
    public function testForbiddenToGuests(): void
    {
        $response = $this->get(route('blogetc.admin.categories.index'));

        $this->assertSame(RedirectResponse::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * Test admin index page lists categories.
     */
    public function testIndexIncludesRecentCategory(): void
    {
        $category = factory(Category::class)->create();

        $this->beLegacyAdminUser();

        $response = $this->get(route('blogetc.admin.categories.index'));

        $response->assertSee($category->title);
        $response->assertViewHas('categories');
    }

    /**
     * Test the create form is displayed.
     */
    public function testCreateForm(): void
    {
        $this->beLegacyAdminUser();
        $response = $this->get(route('blogetc.admin.categories.create_category'));
        $response->assertOk();
    }

    /**
     * Test that new blog categories can be stored.
     */
    public function testStore(): void
    {
        $this->beLegacyAdminUser();

        $params = [
            'category_name'        => $this->faker->sentence,
            'slug'                 => $this->faker->lexify('???????'),
            'category_description' => $this->faker->sentence,
        ];

        $response = $this->post(route('blogetc.admin.categories.store_category'), $params);

        $response->assertRedirect()->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('blog_etc_categories', $params);
    }

    /**
     * Test the edit form.
     */
    public function testEdit(): void
    {
        $this->beLegacyAdminUser();

        $category = factory(Category::class)->create();

        $response = $this->get(route('blogetc.admin.categories.edit_category', $category));

        $response->assertOk()->assertSee($category->title);
    }

    /**
     * Test that trying to edit an invalid Category does not work.
     */
    public function testEditInvalidCategory(): void
    {
        $this->beLegacyAdminUser();

        $invalidID = 9999;

        $response = $this->get(route('blogetc.admin.categories.edit_category', $invalidID));

        $response->assertNotFound();
    }

    /**
     * Test can delete a category.
     */
    public function testDestroy(): void
    {
        $this->beLegacyAdminUser();

        $category = factory(Category::class)->create();

        $response = $this->delete(route('blogetc.admin.categories.destroy_category', $category));

        $response->assertOk()->assertViewIs('blogetc_admin::categories.deleted_category');

        $this->assertDatabaseMissing('blog_etc_categories', ['id' => $category->id]);
    }

    /**
     * Test 404 response when deleting invalid category ID.
     */
    public function testDestroyInvalidCategoryID(): void
    {
        $this->beLegacyAdminUser();

        $invalidCategoryID = 999;
        $response = $this->delete(route('blogetc.admin.categories.destroy_category', $invalidCategoryID));

        $response->assertNotFound();
    }

    /**
     * Test can update a category.
     */
    public function testUpdate(): void
    {
        $this->beLegacyAdminUser();

        $category = factory(Category::class)->create();

        $params = $category->toArray();

        $params['category_name'] = $this->faker->sentence;

        $response = $this->patch(route('blogetc.admin.categories.update_category', $category), $params);

        $response->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('blog_etc_categories', ['category_name' => $params['category_name']]);
    }

    /**
     * Test trying to update a category which does not exist gives a 404 response.
     */
    public function testUpdateInvalidCategoryID(): void
    {
        $invalidCategoryID = 10000;
        $this->beLegacyAdminUser();

        $params = factory(Category::class)->make()->toArray();

        $response = $this->patch(route('blogetc.admin.categories.update_category', $invalidCategoryID), $params);

        $response->assertNotFound();
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
