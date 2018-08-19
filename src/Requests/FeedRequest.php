<?php

namespace WebDevEtc\BlogEtc\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use WebDevEtc\BlogEtc\BaseRequestInterface;

class FeedRequest extends FormRequest
{


    /**
     * Always return true, as this is just to view the rss feed
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     *
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => [Rule::in(['rss','atom'])],
        ];
    }

    /**
     * Is this request for an RSS feed or Atom feed? defaults to atom.
     * @return string
     */
    public function getFeedType()
    {
        return \Request::get("type") === 'rss' ? 'rss' : 'atom';
    }


}
