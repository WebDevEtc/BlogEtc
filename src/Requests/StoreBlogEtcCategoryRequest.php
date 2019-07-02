<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Validation\Rule;

/**
 * Class StoreBlogEtcCategoryRequest
 * @package WebDevEtc\BlogEtc\Requests
 */
class StoreBlogEtcCategoryRequest extends BaseBlogEtcCategoryRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules():array
    {
        $return = $this->baseCategoryRules();
        $return['slug'] [] = Rule::unique('blog_etc_categories', 'slug');
        return $return;
    }
}
