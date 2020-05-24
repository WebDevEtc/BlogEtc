<?php

namespace WebDevEtc\BlogEtc\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use WebDevEtc\BlogEtc\Interfaces\CaptchaInterface;

class AddNewCommentRequest extends FormRequest
{
    public function authorize()
    {
        return 'built_in' === config('blogetc.comments.type_of_comments_to_show');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $return = [
            'comment'        => ['required', 'string', 'min:3', 'max:1000'],
            'author_name'    => ['string', 'min:1', 'max:50'],
            'author_email'   => ['string', 'nullable', 'min:1', 'max:254', 'email'],
            'author_website' => ['string', 'nullable', 'min:'.strlen('http://a.b'), 'max:175', 'active_url'],
        ];

        $return['author_name'][] = Auth::check() && config('blogetc.comments.save_user_id_if_logged_in', true)
            ? 'nullable'
            : 'required';

        if (config('blogetc.captcha.captcha_enabled')) {
            /** @var string $captcha_class */
            $captcha_class = config('blogetc.captcha.captcha_type');

            /** @var CaptchaInterface $captcha */
            $captcha = new $captcha_class();

            $return[$captcha->captcha_field_name()] = $captcha->rules();
        }

        // in case you need to implement something custom, you can use this...
        if (config('blogetc.comments.rules') && is_callable(config('blogetc.comments.rules'))) {
            /** @var callable $func */
            $func = config('blogetc.comments.rules');
            $return = $func($return);
        }

        if (config('blogetc.comments.require_author_email')) {
            $return['author_email'][] = 'required';
        }

        return $return;
    }
}
