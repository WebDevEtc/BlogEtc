<?php

namespace WebDevEtc\BlogEtc\Requests;

class DeleteBlogEtcPostRequest extends BaseAdminRequest
{
    /**
     * No rules needed for this DELETE request - we just need to implement it due to the interface requirement.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
        ];
    }
}
