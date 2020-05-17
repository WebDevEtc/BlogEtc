<?php

namespace WebDevEtc\BlogEtc\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;

/**
 * Class CategoryEdited.
 */
class CategoryEdited
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var BlogEtcCategory */
    public $blogEtcCategory;

    /**
     * CategoryEdited constructor.
     */
    public function __construct(BlogEtcCategory $blogEtcCategory)
    {
        $this->blogEtcCategory = $blogEtcCategory;
    }
}
