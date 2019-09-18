<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DeleteBlogEtcPostRequest
 * @package WebDevEtc\BlogEtc\Requests
 */
class DeleteBlogEtcPostRequest extends FormRequest
{
    /**
     * No rules needed for this DELETE request - we just need to implement it due to the interface requirement.
     *
     * @return array
     */
    public function rules():array
    {
        return [
        ];
    }
}
