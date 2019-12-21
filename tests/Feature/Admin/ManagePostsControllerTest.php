<?php

namespace WebDevEtc\BlogEtc\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\WithFaker;
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
     * (Via gate)
     */
    public function testForbiddenToNonAdminUsers(): void
    {
        $this->beNonAdminUser();

        $response = $this->get(route('blogetc.admin.index'));

        $response->assertForbidden();
    }

    /**
     * Assert that the index admin page is not accessible for guests.
     * (Via auth middleware)
     */
    public function testForbiddenToGuests(): void
    {
        $response = $this->get(route('blogetc.admin.index'));

        $response->assertRedirect(route('login'));
    }

}
