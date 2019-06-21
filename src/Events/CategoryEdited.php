<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;

/**
 * Class CategoryEdited
 * @package WebDevEtc\BlogEtc\Events
 */
class CategoryEdited
{
    use Dispatchable, SerializesModels;

    /** @var BlogEtcCategory */
    public $blogEtcCategory;

    /**
     * CategoryEdited constructor.
     * @param BlogEtcCategory $blogEtcCategory
     */
    public function __construct(BlogEtcCategory $blogEtcCategory)
    {
        $this->blogEtcCategory = $blogEtcCategory;
    }
}
