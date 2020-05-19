<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Category;

/**
 * Class CategoryAdded.
 */
class CategoryAdded
{
    use Dispatchable;
    use SerializesModels;

    /** @var Category */
    public $blogEtcCategory;

    /**
     * CategoryAdded constructor.
     */
    public function __construct(Category $category)
    {
        $this->blogEtcCategory = $category;
    }
}
