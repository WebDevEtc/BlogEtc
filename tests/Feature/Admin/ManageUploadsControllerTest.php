<?php

namespace WebDevEtc\BlogEtc\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\RedirectResponse;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Tests\TestCase;

class ManageUploadsControllerTest extends TestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->featureSetUp();
    }

    public function testNonLoggedInUserForbidden(): void
    {
        $response = $this->get(route('blogetc.admin.images.all'));
        $response->assertUnauthorized();
    }

    public function testNonLoggedInUserForbiddenWithGate(): void
    {
        $this->setAdminGate();
        $response = $this->get(route('blogetc.admin.images.all'));
        $response->assertUnauthorized();
    }

    public function testGatedAdminUserCanViewIndex(): void
    {
        $this->beAdminUserWithGate();
        $response = $this->get(route('blogetc.admin.images.all'));
        $response->assertOk();
    }

    public function testGatedNonAdminUserCannotViewIndex(): void
    {
        $this->beNonAdminUserWithGate();
        $response = $this->get(route('blogetc.admin.images.all'));
        $response->assertUnauthorized();
    }


    public function testLegacyAdminUserCanViewIndex(): void
    {
        $this->beLegacyAdminUser();
        $response = $this->get(route('blogetc.admin.images.all'));
        $response->assertOk();
    }

    public function testLegacyNonAdminUserCannotViewIndex(): void
    {
        $this->beLegacyNonAdminUser();
        $response = $this->get(route('blogetc.admin.images.all'));
        $response->assertUnauthorized();
    }

    // TODO test upload form & storing upload.
}
