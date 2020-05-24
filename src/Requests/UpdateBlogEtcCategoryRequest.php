<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Validation\Rule;

class UpdateBlogEtcCategoryRequest extends BaseBlogEtcCategoryRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $return = $this->baseCategoryRules();
        $return['slug'][] = Rule::unique('blog_etc_categories', 'slug')->ignore($this->route()
            ->parameter('categoryId'));

        return $return;
    }
}
