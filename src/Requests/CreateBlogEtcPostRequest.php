<?php

namespace WebDevEtc\BlogEtc\Requests;


use WebDevEtc\BlogEtc\Requests\Traits\HasCategoriesTrait;
use WebDevEtc\BlogEtc\Requests\Traits\HasImageUploadTrait;

class CreateBlogEtcPostRequest extends BaseRequest
{

    use HasCategoriesTrait;
    use HasImageUploadTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $return = $this->BaseBlogPostRules();
        $return['slug'][] = 'unique:blog_etc_posts,slug'; // creating, so lets make sure it is unique in the whole table
        return $return;
    }

}
