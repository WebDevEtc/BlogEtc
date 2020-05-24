<?php

namespace WebDevEtc\BlogEtc\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use WebDevEtc\BlogEtc\Interfaces\BaseRequestInterface;

/**
 * Class BaseRequest.
 */
abstract class BaseRequest extends FormRequest implements BaseRequestInterface
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
