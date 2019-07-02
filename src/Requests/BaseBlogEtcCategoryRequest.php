<?php

namespace WebDevEtc\BlogEtc\Requests;

/**
 * Class BaseBlogEtcCategoryRequest
 * @package WebDevEtc\BlogEtc\Requests
 */
abstract class BaseBlogEtcCategoryRequest extends BaseRequest
{
    // todo - redo both of the subclasses and just do it once! this is silly at the moment.
    /**
     * Shared rules for categories
     * @return array
     */
    protected function baseCategoryRules():array
    {
        $return = [
            'category_name' => ['required', 'string', 'min:1', 'max:200'],
            'slug' => ['required', 'alpha_dash', 'max:100', 'min:1'],
            'category_description' => ['nullable', 'string', 'min:1', 'max:5000'],
        ];
        return $return;
    }
}
