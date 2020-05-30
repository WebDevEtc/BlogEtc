<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Tests\TestCase;

//use WebDevEtc\BlogEtc\Services\CommentsService;

/**
 * Class PostsControllerTest.
 *
 * Test the comments controller.
 *
 * @todo: Add more tests (different comment providers, captcha, logged in/logged out users, config options).
 */
class CommentsControllerTest extends TestCase
{
    use WithFaker;

    /**
     * Test the store method for saving a new comment.
     */
    public function testStore(): void
    {
        $this->withoutExceptionHandling();
        $post = factory(Post::class)->create();
        $this->beLegacyAdminUser();

        $url = route('blogetc.comments.add_new_comment', $post->slug);

        $params = [
            'comment'        => $this->faker->sentence,
            'author_name'    => $this->faker->name,
            'author_email'   => $this->faker->safeEmail,
            'author_website' => 'http://'.$this->faker->safeEmailDomain,
        ];

        $response = $this->postJson($url, $params);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $postResponse = $this->get(route('blogetc.single', $post->slug));
        $postResponse->assertSee($params['comment']);
    }

    /**
     * Test the store method for saving a new comment.
     */
    public function testDisabledCommentsStore(): void
    {
        config(['blogetc.comments.type_of_comments_to_show' => 'disabled']);

        $post = factory(Post::class)->create();

        $url = route('blogetc.comments.add_new_comment', $post->slug);

        $params = [
            'comment'        => $this->faker->sentence,
            'author_name'    => $this->faker->name,
            'author_email'   => $this->faker->safeEmail,
            'author_website' => 'http://'.$this->faker->safeEmailDomain,
        ];

        $response = $this->postJson($url, $params);

        $response->assertForbidden();

        $this->assertDatabaseMissing('blog_etc_comments', ['comment' => $params['comment']]);
    }

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();

        config(['blogetc.comments.type_of_comments_to_show' => 'built_in']);
        config(['blogetc.comments.auto_approve_comments' => true]);
        config(['blogetc.captcha.captcha_enabled' => false]);
    }
}
