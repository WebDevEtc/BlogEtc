<?php namespace WebDevEtc\BlogEtc\Requests\Traits;

use WebDevEtc\BlogEtc\Models\BlogEtcCategory;

/**
 * Class HasCategoriesTrait - used with Form Requests
 *
 * @package WebDevEtc\BlogEtc\Requests\Traits
 */
trait HasCategoriesTrait
{
    /**
     * If $_GET['category'] slugs were submitted, then it should return an array of the IDs
     *
     * @return array
     */
    public function categories(): array
    {
        // check if categories were submitted, it not return an empty array
        if (!$this->get('category') || !is_array($this->get('category'))) {
            return [];
        }

        // check they are valid, return the IDs
        // limit to 1000 ... just in case someone submits with too many for the web server.
        // No error is given if they submit more than 1k.
        // TODO move to repo calls
        return BlogEtcCategory::whereIn('id', array_keys($this->get('category')))
            ->select('id')
            ->limit(1000)
            ->get()
            ->pluck('id')
            ->toArray();
    }
}
