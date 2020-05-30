<?php

namespace WebDevEtc\BlogEtc\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
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
        $this->middleware(UserCanManageBlogPosts::class);
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
     * @deprecated - use store()
     */
    public function store_category(CategoryRequest $request)
    {
        return $this->store($request);
    }

    /**
     * Store a new category.
     */
    public function store(CategoryRequest $request)
    {
        $this->service->create($request->validated());

        Helpers::flashMessage('Saved new category');

        return redirect(route('blogetc.admin.categories.index'));
    }

    /**
     * @deprecated - use edit()
     */
    public function edit_category($categoryId)
    {
        return $this->edit($categoryId);
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
     * @deprecated - use create()
     */
    public function create_category()
    {
        return $this->create();
    }

    /**
     * Show the form for creating new category.
     */
    public function create(): View
    {
        return view('blogetc_admin::categories.add_category');
    }

    /**
     * @deprecated - use update()
     */
    public function update_category(CategoryRequest $request, $categoryId)
    {
        return $this->update($request, $categoryId);
    }

    /**
     * Save submitted changes.
     *
     * @return RedirectResponse|Redirector
     */
    public function update(CategoryRequest $request, $categoryID)
    {
        $category = $this->service->update($categoryID, $request->validated());

        Helpers::flashMessage('Updated category');

        return redirect($category->editUrl());
    }

    /**
     * @deprecated - use destroy()
     */
    public function destroy_category(CategoryRequest $request, $categoryId)
    {
        return $this->destroy($request, $categoryId);
    }

    /**
     * Delete the category.
     */
    public function destroy(/** @scrutinizer ignore-unused */ CategoryRequest $request, $categoryID)
    {
        $this->service->delete($categoryID);

        return view('blogetc_admin::categories.deleted_category');
    }
}
