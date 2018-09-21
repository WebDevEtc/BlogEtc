<?php

namespace WebDevEtc\BlogEtc\Requests;


class SearchRequest extends BaseRequest
{

    public function authorize()
    {

        if (config("blogetc.search.search_enabled")) {
            // anyone is allowed to submit a comment, to return true always.
            return true;
        }

        //comments are disabled so just return false to disallow everyone.
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            's' => ['nullable', 'string', 'min:3', 'max:40'],
        ];

    }

}
