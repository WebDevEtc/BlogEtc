<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryRequest extends BaseAdminRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        if (Request::METHOD_DELETE === $this->method()) {
            // No rules are required for deleting.
            return [];
        }
        $rules = [
            'category_name'        => ['required', 'string', 'min:1', 'max:200'],
            'slug'                 => ['required', 'alpha_dash', 'max:100', 'min:1'],
            'category_description' => ['nullable', 'string', 'min:1', 'max:5000'],
        ];

        if (Request::METHOD_POST === $this->method()) {
            $rules['slug'][] = Rule::unique('blog_etc_categories', 'slug');
        }

        if (in_array($this->method(), [Request::METHOD_PUT, Request::METHOD_PATCH], true)) {
            $rules['slug'][] = Rule::unique('blog_etc_categories', 'slug')
                ->ignore($this->route()->parameter('categoryId'));
        }

        return $rules;
    }
}
