<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Exceptions\PostNotFoundException;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Repositories\PostsRepository;
use WebDevEtc\BlogEtc\Tests\TestCase;

class PostsRepositoryTest extends TestCase
{
    use WithFaker;

    /** @var PostsRepository */
    protected $postsRepository;

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
        Post::truncate();

        $this->postsRepository = resolve(PostsRepository::class);
    }

    public function testIndexPaginated()
    {
        factory(Post::class, 25)->create();

        $response = $this->postsRepository->indexPaginated(10, null);

        $this->assertSame(25, $response->total());
        $this->assertSame(3, $response->lastPage());
    }

    public function testIndexPaginatedUnpublished()
    {
        factory(Post::class)->create(['is_published' => false]);
        factory(Post::class)->create(['posted_at' => Carbon::now()->addHour()]);

        $response = $this->postsRepository->indexPaginated(10, null);

        $this->assertSame(0, $response->total());
    }

    public function testIndexPaginatedCategoryWithNoPosts()
    {
        $category = factory(Category::class)->create();

        factory(Post::class, 25)->create();

        $response = $this->postsRepository->indexPaginated(10, $category->id);

        $this->assertSame(0, $response->total());
    }

    /**
     * @incomplete
     */
    public function testIndexPaginatedCategory()
    {
        $this->markTestIncomplete('Need to fully test with multiple posts in a category');
        // TODO - go back to this test and check multiple rows.
        // This test is incompleted.
        DB::table('blog_etc_post_categories')->truncate();
        $category = factory(Category::class)->create();

        $posts = factory(Post::class, 3)->create();

        $post1 = $posts->shift();
        DB::table('blog_etc_post_categories')->insert([
            'blog_etc_post_id'     => $post1->id,
            'blog_etc_category_id' => $category->id,
        ]);

//        $post2 = $posts->pop();
//        DB::table('blog_etc_post_categories')->insert([
//            'blog_etc_post_id'     => $post2->id,
//            'blog_etc_category_id' => $category->id,
//        ]);

        $response = $this->postsRepository->indexPaginated(10, $category->id);

//        $this->assertSame(2, $response->total());
        $this->assertSame(1, $response->total());
    }

    public function testRssItems()
    {

        factory(Post::class, 11)->create();

        $response = $this->postsRepository->rssItems();

        $this->assertCount(10, $response);
    }

    public function testRssItemsDoesNotIncludeUnpublished()
    {
        factory(Post::class)->create(['is_published' => false]);
        factory(Post::class)->create(['posted_at' => Carbon::now()->addHour()]);

        $response = $this->postsRepository->rssItems();

        $this->assertEmpty($response);
    }

    public function testSearch(): void
    {
        [$post1, $post2] = factory(Post::class, 2)->create(['title' => 'an example title']);

        $response = $this->postsRepository->search($post1->title);

        $this->assertCount(2, $response);

        $this->assertTrue($response[0]->is($post1));
    }

    public function testSearchDoesNotIncludeUnpublished(): void
    {
        factory(Post::class)->create(['title' => 'test-unpublished', 'is_published' => false]);
        factory(Post::class)->create(['title' => 'test-unpublished', 'posted_at' => Carbon::now()->addDay()]);
        $published = factory(Post::class)->create(['title' => 'test-unpublished']);

        $response = $this->postsRepository->search('test-unpublished');

        $this->assertCount(1, $response);

        $this->assertTrue($response[0]->is($published));
    }

    public function testFindBySlug() : void{
        $post = factory(Post::class)->create();

        $response = $this->postsRepository->findBySlug($post->slug);

        $this->assertTrue($post->is($response));
    }

    public function testFindBySlugFails() : void{
        $this->expectException(PostNotFoundException::class);
        $this->postsRepository->findBySlug('invalid');
    }
    public function testFindBySlugFailsWhenEmpty() : void{
        $this->expectException(PostNotFoundException::class);
$this->postsRepository->findBySlug('');
    }
    public function testFind() : void{
        $post = factory(Post::class)->create();

        $response = $this->postsRepository->find($post->id);

        $this->assertTrue($post->is($response));
    }

    public function testFindFails() : void{
        $this->expectException(PostNotFoundException::class);
        $this->postsRepository->find(0);
    }

    public function testCreate() : void{

        $postAttributes = factory(Post::class)->make()->toArray();

        $this->assertDatabaseMissing('blog_etc_posts', ['title' => $postAttributes['title']]);

        $response = $this->postsRepository->create($postAttributes);

        $this->assertInstanceOf(Post::class, $response);

        $this->assertDatabaseHas('blog_etc_posts',collect($postAttributes)->only(['title', 'subtitle', 'meta_desc', 'post_body', 'is_published',])->toArray());
    }
    public function testDelete() : void{
        $post = factory(Post::class)->create();

        $this->assertDatabaseHas('blog_etc_posts', ['id' => $post->id]);

        $response = $this->postsRepository->delete($post->id);

        $this->assertTrue($response);

        $this->assertDatabaseMissing('blog_etc_posts', ['id' => $post->id]);
    }

    public function testDeleteFails() : void{
        $this->expectException(PostNotFoundException::class);
         $this->postsRepository->delete(0);
    }
}
