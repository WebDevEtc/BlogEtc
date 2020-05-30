<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Exceptions\CategoryNotFoundException;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Services\CategoriesService;
use WebDevEtc\BlogEtc\Tests\TestCase;

class CategoriesServiceTest extends TestCase
{
    use WithFaker;

    /** @var CategoriesService */
    private $categoriesService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();

        $this->categoriesService = resolve(CategoriesService::class);

        Category::truncate();
    }

    public function testIndexPaginated(): void
    {
        factory(Category::class, 25)->create();
        $result = $this->categoriesService->indexPaginated(10);

        $this->assertSame(25, $result->total());
        $this->assertSame(3, $result->lastPage());
    }

    public function testFindBySlug(): void
    {
        $category = factory(Category::class)->create();

        $result = $this->categoriesService->findBySlug($category->slug);

        $this->assertTrue($category->is($result));
    }

    public function testFindBySlugNotFound(): void
    {
        $this->expectException(CategoryNotFoundException::class);
        $this->categoriesService->findBySlug('not-found');
    }

    public function testCreate(): void
    {
        $attributes = factory(Category::class)->make()->toArray();

        $result = $this->categoriesService->create($attributes);

        $this->assertInstanceOf(Category::class, $result);

        $this->assertDatabaseHas('blog_etc_categories', $attributes);
    }

    public function testUpdate(): void
    {
        $category = factory(Category::class)->create();

        $updatedCategory = $this->categoriesService->update($category->id, ['category_name' => 'updated']);

        $this->assertSame('updated', $updatedCategory->category_name);

        $this->assertDatabaseHas('blog_etc_categories', ['id' => $category->id, 'category_name' => 'updated']);
    }

    public function testUpdateNotFound(): void
    {
        $this->expectException(CategoryNotFoundException::class);

        $this->categoriesService->update(0, ['category_name' => 'updated']);
    }

    public function testFind(): void
    {
        $category = factory(Category::class)->create();

        $result = $this->categoriesService->find($category->id);

        $this->assertTrue($category->is($result));
    }

    public function testFindNotFound(): void
    {
        $this->expectException(CategoryNotFoundException::class);
        $this->categoriesService->find(0);
    }

    public function testDelete(): void
    {
        $category = factory(Category::class)->create();

        $this->categoriesService->delete($category->id);

        $this->assertDatabaseMissing('blog_etc_categories', ['id' => $category->id]);
    }

    public function testDeleteNotFound(): void
    {
        $this->expectException(CategoryNotFoundException::class);

        $this->categoriesService->delete(0);
    }
}
