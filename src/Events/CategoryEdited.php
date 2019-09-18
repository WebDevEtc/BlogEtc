<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Category;

/**
 * Class CategoryEdited
 * @package WebDevEtc\BlogEtc\Events
 */
class CategoryEdited
{
    use Dispatchable, SerializesModels;

    /** @var Category */
    public $blogEtcCategory;

    /**
     * CategoryEdited constructor.
     * @param Category $blogEtcCategory
     */
    public function __construct(Category $blogEtcCategory)
    {
        $this->blogEtcCategory = $blogEtcCategory;
    }
}
