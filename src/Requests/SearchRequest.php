<?php

namespace WebDevEtc\BlogEtc\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class SearchRequest.
 */
class SearchRequest extends FormRequest
{
    /**
     * Can user view the search section?
     */
    public function authorize(): bool
    {
        return true === config('blogetc.search.search_enabled');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            's' => ['nullable', 'string', 'min:3', 'max:40'],
        ];
    }

    /**
     * Return the query that user searched for.
     */
    public function searchQuery(): string
    {
        return $this->get('s', '');
    }
}
