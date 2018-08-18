<?php

namespace WebDevEtc\BlogEtc\Requests;


use WebDevEtc\BlogEtc\Models\BlogEtcCategory;

class UpdateBlogEtcCategoryRequest  extends BaseRequest {


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $return = $this->baseCategoryRules();


            $return['slug'] [] =   function($attribute, $value, $fail) {
                // as this is UPDATING, we want to ensure the ->slug value is not equal to any other value in the table (not including its own row)

                // get the category id, based on the route:
                $this_category_id = \Request::route()->parameter("categoryId");

                if (BlogEtcCategory::where("slug",$value)
                    ->where("id","!=",$this_category_id)
                    ->exists()
                ) {
                    // a row existed with the same slug...
                    return $fail($attribute . ' is invalid - another category is using the slug. Please enter a different (unique) value.');
                }
            };


        return $return;

    }
}
