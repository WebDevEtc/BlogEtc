<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Validation\Rule;
use WebDevEtc\BlogEtc\Requests\Traits\HasCategoriesTrait;
use WebDevEtc\BlogEtc\Requests\Traits\HasImageUploadTrait;

/**
 * Class CreateBlogEtcPostRequest
 * @package WebDevEtc\BlogEtc\Requests
 */
class CreateBlogEtcPostRequest extends BaseBlogEtcPostRequest
{
    use HasCategoriesTrait;
    use HasImageUploadTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $return = $this->baseBlogPostRules();
        $return['slug'] [] = Rule::unique('blog_etc_posts', 'slug');
        return $return;
    }
}
