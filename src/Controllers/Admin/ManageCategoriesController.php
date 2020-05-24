<?php

namespace WebDevEtc\BlogEtc\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
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
     * @deprecated - use store()
     */
    public function store_category(StoreBlogEtcCategoryRequest $request)
    {
        return $this->store($request);
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
    public function update_category(UpdateBlogEtcCategoryRequest $request, $categoryId)
    {
        return $this->update($request, $categoryId);
    }

    /**
     * Save submitted changes.
     *
     * @param $categoryId
     *
     * @return RedirectResponse|Redirector
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
     * @deprecated - use destroy()
     */
    public function destroy_category(DeleteBlogEtcCategoryRequest $request, $categoryId)
    {
        return $this->destroy($request, $categoryId);
    }

    /**
     * Delete the category.
     *
     * @param $categoryId
     *
     * @return Factory|View
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
}
