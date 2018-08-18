<?php

namespace WebDevEtc\BlogEtc\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use WebDevEtc\BlogEtc\BaseRequestInterface;

class FeedRequest extends FormRequest
{


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    public function getFeedType()
    {
        return \Request::get("type") === 'rss' ? 'rss' : 'atom';
    }


}
