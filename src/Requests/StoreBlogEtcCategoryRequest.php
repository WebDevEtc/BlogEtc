<?php

namespace WebDevEtc\BlogEtc\Requests;



class StoreBlogEtcCategoryRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $return = $this->baseCategoryRules();
        $return['slug'][] = "unique:blog_etc_categories,slug";
        return $return;
    }
}
