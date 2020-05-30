<?php

namespace WebDevEtc\BlogEtc\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Tests\TestCase;

class ManageCommentsControllerTest extends TestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->featureSetUp();
    }

    public function testNonLoggedInUserForbidden(): void
    {
        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertUnauthorized();
    }

    public function testNonLoggedInUserForbiddenWithGate(): void
    {
        $this->setAdminGate();
        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertUnauthorized();
    }

    public function testGatedAdminUserCanViewIndex(): void
    {
        $this->beAdminUserWithGate();
        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertOk();
    }

    public function testGatedNonAdminUserCannotViewIndex(): void
    {
        $this->beNonAdminUserWithGate();
        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertUnauthorized();
    }

    public function testLegacyAdminUserCanViewIndex(): void
    {
        $this->beLegacyAdminUser();
        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertOk();
    }

    public function testLegacyNonAdminUserCannotViewIndex(): void
    {
        $this->beLegacyNonAdminUser();
        $response = $this->get(route('blogetc.admin.comments.index'));
        $response->assertUnauthorized();
    }

    public function testCommentsIndex(): void
    {
        $comment = factory(Comment::class)->create();

        $this->beLegacyAdminUser();
        $response = $this->get(route('blogetc.admin.comments.index'));

        $response->assertSee($comment->comment);
    }

    public function testApproveComment(): void
    {
        $this->beAdminUserWithGate();
        $comment = factory(Comment::class)->create(['approved'=> false]);

        $response = $this->patch(route('blogetc.admin.comments.approve', $comment->id));

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('blog_etc_comments', ['id' => $comment->id, 'approved' => true]);
    }

    public function testApproveNonExistingComment(): void
    {
        $this->beAdminUserWithGate();
        $response = $this->patch(route('blogetc.admin.comments.approve', 0));
        $response->assertNotFound();
    }

    public function testDenyComment(): void
    {
        $this->beAdminUserWithGate();
        $response = $this->delete(route('blogetc.admin.comments.delete', 0));
        $response->assertNotFound();
    }
}
