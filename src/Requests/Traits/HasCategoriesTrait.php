<?php namespace WebDevEtc\BlogEtc\Requests\Traits;

use WebDevEtc\BlogEtc\Models\BlogEtcCategory;

trait HasCategoriesTrait
{

    public function categories()
    {
        if (!$this->get("category")) { return []; }
            // check they are valid

            $vals= BlogEtcCategory::whereIn("id",array_keys($this->get("category")))->select("id")->limit(1000)->get();

        $vals=array_values($vals->pluck("id")->toArray());

        return $vals;
        }

}