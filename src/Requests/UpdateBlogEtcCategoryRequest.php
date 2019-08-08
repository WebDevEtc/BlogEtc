<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Validation\Rule;

/**
 * Class UpdateBlogEtcCategoryRequest
 * @package WebDevEtc\BlogEtc\Requests
 */
class UpdateBlogEtcCategoryRequest extends BaseBlogEtcCategoryRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules():array
    {
        $rules = $this->baseCategoryRules();
        $rules['slug'] [] = Rule::unique('blog_etc_categories', 'slug')->ignore($this->route()
            ->parameter('categoryId'));
        return $rules;
    }
}
