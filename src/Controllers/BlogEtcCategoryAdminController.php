<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Requests\DeleteBlogEtcCategoryRequest;
use WebDevEtc\BlogEtc\Requests\StoreBlogEtcCategoryRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcCategoryRequest;
use WebDevEtc\BlogEtc\Services\BlogEtcCategoriesService;

/**
 * Class BlogEtcCategoryAdminController
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcCategoryAdminController extends Controller
{
    /**
     * @var BlogEtcCategoriesService
     */
    private $service;

    /**
     * BlogEtcCategoryAdminController constructor.
     * @param BlogEtcCategoriesService $service
     */
    public function __construct(BlogEtcCategoriesService $service)
    {
        $this->service = $service;

        $this->middleware(UserCanManageBlogPosts::class);
    }

    /**
     * Show list of categories
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
     * Show the form for creating new category
     *
     * @return View
     */
    public function create():View
    {
        return view('blogetc_admin::categories.add_category');
    }

    /**
     * Store a new category
     *
     * @param StoreBlogEtcCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreBlogEtcCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        Helpers::flash_message('Saved new category');

        return redirect(route('blogetc.admin.categories.index'));
    }

    /**
     * Show the edit form for category
     *
     * @param $categoryID
     * @return View
     */
    public function edit($categoryID): View
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
     * Update a blog category attributes
     *
     * @param UpdateBlogEtcCategoryRequest $request
     * @param $categoryID
     * @return RedirectResponse
     */
    public function update(UpdateBlogEtcCategoryRequest $request, $categoryID): RedirectResponse
    {
        $category = $this->service->update($categoryID, $request->validated());

        Helpers::flash_message('Saved category changes');

        return redirect($category->edit_url());
    }

    /**
     * Delete the category
     *
     * @param DeleteBlogEtcCategoryRequest $request
     * @param $categoryID
     * @return Factory|View
     */
    public function destroy(DeleteBlogEtcCategoryRequest $request, $categoryID)
    {
        $this->service->delete($categoryID);

        return view('blogetc_admin::categories.deleted_category');
    }
}
