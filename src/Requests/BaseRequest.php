<?php

namespace WebDevEtc\BlogEtc\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BaseRequest
 * @package WebDevEtc\BlogEtc\Requests
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check() && Auth::user()->canManageBlogEtcPosts();
    }
}
