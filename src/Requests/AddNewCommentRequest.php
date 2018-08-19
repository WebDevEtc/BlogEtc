<?php

namespace WebDevEtc\BlogEtc\Requests;


class AddNewCommentRequest extends BaseRequest
{

    public function authorize()
    {

        if (config("blogetc.comments.type_of_comments_to_show") !== 'disabled') {
            // anyone is allowed to submit a comment, to return true always.
            return true;
        }


        //comments are disabled.
        return false;
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
