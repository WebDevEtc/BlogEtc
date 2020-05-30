<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Exceptions\PostNotFoundException;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Services\PostsService;
use WebDevEtc\BlogEtc\Tests\TestCase;

class PostsServiceTest extends TestCase
{
    use WithFaker;

    /** @var PostsService */
    private $postsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();

        $this->postsService = resolve(PostsService::class);
        Post::truncate();
    }

    public function testIndexPaginated()
    {
        factory(Post::class, 25)->create();

        $response = $this->postsService->indexPaginated(10, null);

        $this->assertSame(25, $response->total());
        $this->assertSame(3, $response->lastPage());
    }

    public function testIndexPaginatedUnpublished()
    {
        factory(Post::class)->create(['is_published' => false]);
        factory(Post::class)->create(['posted_at' => Carbon::now()->addHour()]);

        $response = $this->postsService->indexPaginated(10, null);

        $this->assertSame(0, $response->total());
    }

    public function testIndexPaginatedCategoryWithNoPosts()
    {
        $category = factory(Category::class)->create();

        factory(Post::class, 25)->create();

        $response = $this->postsService->indexPaginated(10, $category->id);

        $this->assertSame(0, $response->total());
    }

    public function testRssItems()
    {
        factory(Post::class, 11)->create();

        $response = $this->postsService->rssItems();

        $this->assertCount(10, $response);
    }

    public function testRssItemsDoesNotIncludeUnpublished()
    {
        factory(Post::class)->create(['is_published' => false]);
        factory(Post::class)->create(['posted_at' => Carbon::now()->addHour()]);

        $response = $this->postsService->rssItems();

        $this->assertEmpty($response);
    }

    public function testSearch(): void
    {
        [$post1, $post2] = factory(Post::class, 2)->create(['title' => 'an example title']);

        $response = $this->postsService->search($post1->title);

        $this->assertCount(2, $response);

        $this->assertTrue($response[0]->is($post1));
    }

    public function testSearchDoesNotIncludeUnpublished(): void
    {
        factory(Post::class)->create(['title' => 'test-unpublished', 'is_published' => false]);
        factory(Post::class)->create(['title' => 'test-unpublished', 'posted_at' => Carbon::now()->addDay()]);
        $published = factory(Post::class)->create(['title' => 'test-unpublished']);

        $response = $this->postsService->search('test-unpublished');

        $this->assertCount(1, $response);

        $this->assertTrue($response[0]->is($published));
    }

    public function testFindBySlug(): void
    {
        $post = factory(Post::class)->create();

        $response = $this->postsService->findBySlug($post->slug);

        $this->assertTrue($post->is($response));
    }

    public function testFindBySlugFails(): void
    {
        $this->expectException(PostNotFoundException::class);
        $this->postsService->findBySlug('invalid');
    }

    public function testFindBySlugFailsWhenEmpty(): void
    {
        $this->expectException(PostNotFoundException::class);
        $this->postsService->findBySlug('');
    }
}
