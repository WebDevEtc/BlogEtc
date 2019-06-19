<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;

/**
 * Class CategoryAdded
 * @package WebDevEtc\BlogEtc\Events
 */
class CategoryAdded
{
    use Dispatchable, SerializesModels;

    /** @var  BlogEtcCategory */
    public $blogEtcCategory;

    /**
     * CategoryAdded constructor.
     * @param BlogEtcCategory $blogEtcCategory
     */
    public function __construct(BlogEtcCategory $blogEtcCategory)
    {
        $this->blogEtcCategory = $blogEtcCategory;
    }
}
