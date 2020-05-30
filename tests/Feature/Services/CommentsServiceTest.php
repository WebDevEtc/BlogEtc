<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Exceptions\CommentNotFoundException;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Services\CommentsService;
use WebDevEtc\BlogEtc\Tests\TestCase;

class CommentsServiceTest extends TestCase
{
    use WithFaker;

    /** @var CommentsService */
    protected $commentsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();

        $this->commentsService = resolve(CommentsService::class);
        Comment::truncate();
    }

    public function testApprove()
    {
        $comment = factory(Comment::class)->create(['approved' => false]);

        $this->commentsService->approve($comment->id);

        $comment->refresh();

        $this->assertTrue($comment->fresh()->approved);
    }

    /**
     * Approving an already approved comment should still work.
     */
    public function testApproveAlreadyApproved()
    {
        $comment = factory(Comment::class)->create(['approved' => true]);
        $this->commentsService->approve($comment->id);
        $this->assertTrue($comment->fresh()->approved);
    }

    public function testApproveNonExistingComment()
    {
        $this->expectException(CommentNotFoundException::class);
        $this->commentsService->approve(0);
    }

    public function testFind()
    {
        $comment = factory(Comment::class)->create();

        $response = $this->commentsService->find($comment->id);

        $this->assertTrue($comment->is($response));
    }

    public function testFindApproved()
    {
        $comment = factory(Comment::class)->create(['approved' => true]);
        $response = $this->commentsService->find($comment->id, true);
        $this->assertTrue($comment->is($response));
    }

    public function testFindNonApproved()
    {
        $comment = factory(Comment::class)->create(['approved' => false]);
        $this->expectException(CommentNotFoundException::class);
        $this->commentsService->find($comment->id, true);
    }

    public function testFindNonExisting()
    {
        $this->expectException(CommentNotFoundException::class);
        $this->commentsService->find(0);
    }

    public function testCreate()
    {
        $post = factory(Post::class)->create();

        $commentText = $this->faker->sentence;
        $this->commentsService->create($post, ['comment' => $commentText], '127.0.0.1', null);

        $this->assertDatabaseHas('blog_etc_comments', ['comment' => $commentText, 'blog_etc_post_id' => $post->id, 'approved' => false]);
    }
}
