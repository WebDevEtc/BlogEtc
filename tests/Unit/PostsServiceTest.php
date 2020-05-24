<?php

namespace WebDevEtc\BlogEtc\Tests\Unit;

use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Events\BlogPostWillBeDeleted;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Repositories\PostsRepository;
use WebDevEtc\BlogEtc\Services\PostsService;
use WebDevEtc\BlogEtc\Tests\TestCase;

/**
 * Class PostsServiceTest.
 *
 * Unit test for PostsService.
 *
 * Should be quick to run, mock any DB calls.
 */
class PostsServiceTest extends TestCase
{
    use WithFaker;

    /**
     * Set up for all tests.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->featureSetUp();

        config(['blogetc' => ['image_upload_enabled' => false]]);
    }

    /**
     * Test that the repository() method will return an instance of PostsRepository.
     */
    public function testRepository(): void
    {
        $mock = $this->mock(PostsRepository::class);

        $service = resolve(PostsService::class);

        $repository = $service->repository();

        $this->assertSame($mock, $repository);
    }

    /**
     * Test that calling create will call the correct method on the PostsRepository.
     * We can assume the repo creates the database entry.
     */
    public function testCreate(): void
    {
        $this->markTestSkipped('Skipping as PostsService::create() is not yet ready for prod/testing');
//        $this->mock(PostsRepository::class, static function ($mock) {
//            $mock->shouldReceive('create')->once();
//        });
//
//        $service = resolve(PostsService::class);
//
//        $request = $this->createRequest($this->createParams());
//
//        $service->create($request, null);
    }

    /**
     * Test that indexPaginated calls the correct repository method.
     */
    public function testIndexPaginated(): void
    {
        $this->mock(PostsRepository::class, static function ($mock) {
            $mock->shouldReceive('indexPaginated')->once();
        });

        $service = resolve(PostsService::class);

        $service->indexPaginated();
    }

    /**
     * Test that findBySlug calls the correct repository method.
     */
    public function testFindBySlug(): void
    {
        $this->mock(PostsRepository::class, static function ($mock) {
            $mock->shouldReceive('findBySlug')->once();
        });

        $service = resolve(PostsService::class);

        $service->findBySlug('test');
    }

    /**
     * Test that rssItems calls the correct repository method.
     */
    public function testRssItems(): void
    {
        $this->mock(PostsRepository::class, static function ($mock) {
            $mock->shouldReceive('rssItems')->once();
        });

        $service = resolve(PostsService::class);

        $service->rssItems();
    }

    /**
     * Test that the update method calls the correct repo calls.
     *
     * The update() method works directly on the Eloquent model - this should be refactored.
     */
    public function testUpdate(): void
    {
        $this->markTestSkipped('Skipping as PostsService::update() is not yet ready for prod/testing');
//        $belongsToMany = $this->mock(BelongsToMany::class, static function ($mock) {
//            $mock->shouldReceive('sync');
//        });
//
//        $mockedModel = $this->mock(Post::class, static function ($mock) use ($belongsToMany) {
//            $mock->shouldReceive('fill')->once();
//            $mock->shouldReceive('save')->once();
//            $mock->shouldReceive('categories')->andReturn($belongsToMany);
//        });
//
//        $this->mock(PostsRepository::class, static function ($mock) use ($mockedModel) {
//            $mock->shouldReceive('find')->once()->andReturn($mockedModel);
//        });
//
//        $this->mock(UploadsService::class, static function ($mock) {
//            $mock->shouldReceive('processFeaturedUpload')->once();
//        });
//
//        $service = resolve(PostsService::class);
//
//        $request = $this->createRequest($this->createParams());
//
//        $this->expectsEvents(BlogPostEdited::class);
//
//        $service->update(1, $request);
    }

    /**
     * Test the delete() service call.
     *
     * @throws Exception
     */
    public function testDelete(): void
    {
        $this->markTestSkipped('Skipping as PostsService::delete() is not yet ready for prod/testing');
        $this->mock(PostsRepository::class, static function ($mock) {
            $mock->shouldReceive('find')->with(123)->andReturn(new Post());
            $mock->shouldReceive('delete')->with(123)->andReturn(true);
        });

        $service = resolve(PostsService::class);

        $this->expectsEvents(BlogPostWillBeDeleted::class);

        $response = $service->delete(123);

        $this->assertIsArray($response);
    }

    /**
     * Helper method to set up the params for editing/creating.
     */
    private function createParams(): array
    {
        $this->markTestSkipped('Skipping as current version does not include PostService - keeping tests in to make migration easier (in theory...) later');

        return [
            'posted_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'title' => $this->faker->sentence,
            'subtitle' => $this->faker->sentence,
            'post_body' => $this->faker->paragraph,
            'meta_desc' => $this->faker->paragraph,
            'short_description' => $this->faker->paragraph,
            'slug' => $this->faker->asciify('*********'),
            'categories' => null,
        ];
    }

//    private function createRequest(array $params): PostRequest
//    {
//        $this->markTestSkipped('Skipping as current version does not include PostService - keeping tests in to make migration easier (in theory...) later');
//        $mockedValidator = $this->mock(Validator::class, static function ($mock) use ($params) {
//            $mock->shouldReceive('validated')->andReturn($params);
//        });
//
//        $request = PostRequest::create('/posts/add', Request::METHOD_POST, $params);
//
//        return tap($request)->setValidator($mockedValidator);
//    }
//
    public function testSearch(): void
    {
        $post = factory(Post::class)->create();
        $otherPost = factory(Post::class)->create();

        /** @var PostsService $service */
        $service = resolve(PostsService::class);

        $response = $service->search($post->title);

        $this->assertCount(1, $response);

        $this->assertTrue($post->is($response->first()));
        $this->assertFalse($otherPost->is($response->first()));
    }

    public function testSearchNoResults(): void
    {
        factory(Post::class)->create();

        /** @var PostsService $service */
        $service = resolve(PostsService::class);

        $response = $service->search('do not find');

        $this->assertEmpty($response);
    }

    public function testSearchEmpty(): void
    {
        factory(Post::class)->create();

        /** @var PostsService $service */
        $service = resolve(PostsService::class);

        $response = $service->search('');

        $this->assertEmpty($response);
    }

    public function testSearchLimit(): void
    {
        $posts = factory(Post::class, 10)->create(['title' => 'search-limit-title']);

        /** @var PostsService $service */
        $service = resolve(PostsService::class);

        $response = $service->search($posts->first()->title, 10);

        $this->assertCount(10, $response);
    }
}
