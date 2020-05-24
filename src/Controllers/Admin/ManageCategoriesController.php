<?php

namespace WebDevEtc\BlogEtc\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use WebDevEtc\BlogEtc\Events\CategoryAdded;
use WebDevEtc\BlogEtc\Events\CategoryEdited;
use WebDevEtc\BlogEtc\Events\CategoryWillBeDeleted;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\Category;
use WebDevEtc\BlogEtc\Requests\DeleteBlogEtcCategoryRequest;
use WebDevEtc\BlogEtc\Requests\StoreBlogEtcCategoryRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcCategoryRequest;
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
     * Show the form for creating new category.
     */
    public function create(): View
    {
        return view('blogetc_admin::categories.add_category');
    }

    /**
     * Store a new category.
     */
    public function store(StoreBlogEtcCategoryRequest $request)
    {
        $new_category = Category::create($request->validated());

        Helpers::flashMessage('Created new category');

        event(new CategoryAdded($new_category));

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
     * Save submitted changes.
     *
     * @param $categoryId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(UpdateBlogEtcCategoryRequest $request, $categoryId)
    {
        /** @var Category $category */
        $category = Category::findOrFail($categoryId);
        $category->fill($request->validated());
        $category->save();

        Helpers::flashMessage('Saved category changes');
        event(new CategoryEdited($category));

        return redirect($category->edit_url());
    }

    /**
     * Delete the category.
     *
     * @param $categoryId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function destroy(DeleteBlogEtcCategoryRequest $request, $categoryId)
    {
        /* Please keep this in, so code inspections don't say $request was unused. Of course it might now get marked as left/right parts are equal */
        $request = $request;

        $category = Category::findOrFail($categoryId);
        event(new CategoryWillBeDeleted($category));
        $category->delete();

        return view('blogetc_admin::categories.deleted_category');
    }

    /**
     * @deprecated - use store()
     */
    public function store_category(StoreBlogEtcCategoryRequest $request)
    {
        return $this->store($request);
    }

    /**
     * @deprecated - use edit()
     */
    public function edit_category($categoryId)
    {
        return $this->edit($categoryId);
    }

    /**
     * @deprecated - use create()
     */
    public function create_category()
    {
        return $this->create();
    }

    /**
     * @deprecated - use update()
     */
    public function update_category(UpdateBlogEtcCategoryRequest $request, $categoryId)
    {
        return $this->update($request, $categoryId);
    }

    /**
     * @deprecated - use destroy()
     */
    public function destroy_category(DeleteBlogEtcCategoryRequest $request, $categoryId)
    {
        return $this->destroy($request, $categoryId);
    }
}
