<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Exceptions\CommentNotFoundException;
use WebDevEtc\BlogEtc\Models\Comment;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Repositories\CommentsRepository;
use WebDevEtc\BlogEtc\Tests\TestCase;

class CommentsRepositoryTest extends TestCase
{
    use WithFaker;
    /** @var CommentsRepository */
    private $commentsRepository;

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
        Comment::truncate();

        $this->commentsRepository = resolve(CommentsRepository::class);
    }

    public function testApprove()
    {
        $comment = factory(Comment::class)->create(['approved' => false]);

        $this->commentsRepository->approve($comment->id);

        $comment->refresh();

        $this->assertTrue($comment->fresh()->approved);
    }

    /**
     * Approving an already approved comment should still work.
     */
    public function testApproveAlreadyApproved()
    {
        $comment = factory(Comment::class)->create(['approved' => true]);
        $this->commentsRepository->approve($comment->id);
        $this->assertTrue($comment->fresh()->approved);
    }

    public function testApproveNonExistingComment()
    {
        $this->expectException(CommentNotFoundException::class);
        $this->commentsRepository->approve(0);
    }

    public function testFind()
    {
        $comment = factory(Comment::class)->create();

        $response = $this->commentsRepository->find($comment->id);

        $this->assertTrue($comment->is($response));
    }

    public function testFindApproved()
    {
        $comment = factory(Comment::class)->create(['approved' => true]);
        $response = $this->commentsRepository->find($comment->id, true);
        $this->assertTrue($comment->is($response));
    }

    public function testFindNonApproved()
    {
        $comment = factory(Comment::class)->create(['approved' => false]);
        $this->expectException(CommentNotFoundException::class);
        $this->commentsRepository->find($comment->id, true);
    }

    public function testFindNonExisting()
    {
        $this->expectException(CommentNotFoundException::class);
        $response = $this->commentsRepository->find(0);
    }

    public function testCreate()
    {
        $post = factory(Post::class)->create();

        $commentText = $this->faker->sentence;
        $this->commentsRepository->create($post, ['comment' => $commentText], '127.0.0.1', null, null, null, false);

        $this->assertDatabaseHas('blog_etc_comments', ['comment' => $commentText, 'blog_etc_post_id' => $post->id, 'approved' => false]);
    }

    public function testCreateAutoApproved()
    {
        $post = factory(Post::class)->create();

        $commentText = $this->faker->sentence;
        $this->commentsRepository->create($post, ['comment' => $commentText], '127.0.0.1', null, null, null, true);

        $this->assertDatabaseHas('blog_etc_comments', ['comment' => $commentText, 'approved' => true]);
    }
}
