<?php

namespace WebDevEtc\BlogEtc\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Requests\CategoryRequest;
use WebDevEtc\BlogEtc\Services\CategoriesService;

/**
 * Class ManageCategoriesController.
 */
class ManageCategoriesController extends Controller
{
    /** @var CategoriesService */
    private $service;

    /**
     * BlogEtcCategoryAdminController constructor.
     */
    public function __construct(CategoriesService $service)
    {
        $this->service = $service;
    }

    /**
     * Show list of categories.
     *
     * @return mixed
     */
    public function index(): View
    {
        $categories = $this->service->indexPaginated();

        return view(
            'blogetc_admin::categories.index',
            [
                'categories' => $categories,
            ]
        );
    }

    /**
     * Show the form for creating new category.
     */
    public function create(): View
    {
        return view('blogetc_admin::categories.add_category');
    }

    /**
     * Store a new category.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        Helpers::flashMessage('Saved new category');

        return redirect(route('blogetc.admin.categories.index'));
    }

    /**
     * Show the edit form for category.
     */
    public function edit(int $categoryID): View
    {
        $category = $this->service->find($categoryID);

        return view(
            'blogetc_admin::categories.edit_category',
            [
                'category' => $category,
            ]
        );
    }

    /**
     * Update a blog category attributes.
     */
    public function update(CategoryRequest $request, int $categoryID): RedirectResponse
    {
        $category = $this->service->update($categoryID, $request->validated());

        Helpers::flashMessage('Updated category');

        return redirect($category->editUrl());
    }

    /**
     * Delete the category.
     *
     * @throws Exception
     */
    public function destroy(CategoryRequest $request, int $categoryID): View
    {
        $this->service->delete($categoryID);

        return view('blogetc_admin::categories.deleted_category');
    }
}
