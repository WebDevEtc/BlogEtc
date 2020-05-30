<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Exceptions\CategoryNotFoundException;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Repositories\CategoriesRepository;
use WebDevEtc\BlogEtc\Tests\TestCase;

class CategoriesRepositoryTest extends TestCase
{
    use WithFaker;

    /** @var \WebDevEtc\BlogEtc\Repositories\CategoriesRepository */
    private $categoriesRepository;

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
        Category::truncate();

        $this->categoriesRepository = resolve(CategoriesRepository::class);
    }

    public function testIndexPaginated()
    {
        factory(Category::class, 30)->create();

        $response = $this->categoriesRepository->indexPaginated(25);

        $this->assertSame(30, $response->total());
        $this->assertSame(2, $response->lastPage());
    }

    public function testFind()
    {
        $category = factory(Category::class)->create();
        $response = $this->categoriesRepository->find($category->id);
        $this->assertTrue($category->is($response));
    }

    public function testFindNonExisting()
    {
        $this->expectException(CategoryNotFoundException::class);

        $this->categoriesRepository->find(0);
    }

    public function testFindBySlug()
    {
        $category = factory(Category::class)->create();
        $response = $this->categoriesRepository->findBySlug($category->slug);
        $this->assertTrue($category->is($response));
    }

    public function testFindBySlugNonExisting()
    {
        $this->expectException(CategoryNotFoundException::class);

        $this->categoriesRepository->findBySlug('non-existing');
    }
}
