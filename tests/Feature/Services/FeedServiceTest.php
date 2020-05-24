<?php

namespace WebDevEtc\BlogEtc\Tests\Feature;

use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\WithFaker;
use WebDevEtc\BlogEtc\Exceptions\PostNotFoundException;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Services\FeedService;
use WebDevEtc\BlogEtc\Tests\TestCase;

class PostsRepositoryTest extends TestCase
{
    use WithFaker;

    /** @var FeedService */
    protected $feedService;

    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
        Post::truncate();

        $this->feedService = resolve(FeedService::class);
    }


}

