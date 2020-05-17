<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class FeedRequest.
 */
class FeedRequest extends FormRequest
{
    /**
     * Rules for requesting the feed.
     */
    public function rules(): array
    {
        return [
            'type' => [Rule::in(['rss', 'atom'])],
        ];
    }

    /**
     * Is this request for an RSS feed or Atom feed? defaults to atom.
     */
    public function getFeedType(): string
    {
        return 'rss' === $this->get('type') ? 'rss' : 'atom';
    }
}
