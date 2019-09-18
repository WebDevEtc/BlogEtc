<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\Category;

/**
 * Class CategoryWillBeDeleted
 * @package WebDevEtc\BlogEtc\Events
 */
class CategoryWillBeDeleted
{
    use Dispatchable, SerializesModels;

    /** @var Category */
    public $blogEtcCategory;

    /**
     * CategoryWillBeDeleted constructor.
     * @param Category $blogEtcCategory
     */
    public function __construct(Category $blogEtcCategory)
    {
        $this->blogEtcCategory = $blogEtcCategory;
    }
}
