<?php

namespace WebDevEtc\BlogEtc\Requests;


use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\Traits\HasCategoriesTrait;
use WebDevEtc\BlogEtc\Requests\Traits\HasImageUploadTrait;

class UpdateBlogEtcPostRequest  extends BaseRequest {

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

        $return['slug'][] =   function($attribute, $value, $fail) {
            // as this is UPDATING, we want to ensure the ->slug value is not equal to any other value in the table (not including its own row)

            // get the BlogEtcPost id, based on the route:
            $this_post_id = \Request::route()->parameter("blogPostId");

            if (BlogEtcPost::where("slug",$value)
                ->where("id","!=",$this_post_id)
                ->exists()
            ) {
                // a row existed with the same slug...
                return $fail($attribute . ' is invalid - another blog post is using the slug. Please enter a different (unique) value.');
            }
        };

        return $return;
    }
}
