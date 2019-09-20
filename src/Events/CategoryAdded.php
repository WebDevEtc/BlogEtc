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
    use Dispatchable, SerializesModels;

    /** @var Category */
    public $blogEtcCategory;

    /**
     * CategoryAdded constructor.
     *
     * @param Category $blogEtcCategory
     */
    public function __construct(Category $blogEtcCategory)
    {
        $this->blogEtcCategory = $blogEtcCategory;
    }
}
