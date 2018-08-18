<?php

namespace WebDevEtc\BlogEtc\Requests;


class AddNewCommentRequest extends BaseRequest
{

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

        $return = [
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
            'author_name' => ['string', 'min:1', 'max:50']
        ];


        if (\Auth::check() && config("blogetc.comments.save_user_id_if_logged_in", true)) {
            // is logged in, so we don't need an author name (it won't get used)
            $return['author_name'][] = 'nullable';
        } else {
            // is a guest - so we require this
            $return['author_name'][] = 'required';
        }

        return $return;


    }

}
