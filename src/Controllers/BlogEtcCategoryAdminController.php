<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\Events\CategoryAdded;
use WebDevEtc\BlogEtc\Events\CategoryEdited;
use WebDevEtc\BlogEtc\Events\CategoryWillBeDeleted;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\BlogEtcCategory;
use WebDevEtc\BlogEtc\Requests\DeleteBlogEtcCategoryRequest;
use WebDevEtc\BlogEtc\Requests\StoreBlogEtcCategoryRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcCategoryRequest;

class BlogEtcCategoryAdminController extends Controller
{


    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
    }

    public function index(){

        $categories = BlogEtcCategory::orderBy("category_name")->paginate(25);
        return view("blogetc_admin::categories.index")->withCategories($categories);
    }
    public function create_category(){

        return view("blogetc_admin::categories.add_category");

    }
    public function store_category(StoreBlogEtcCategoryRequest $request){
        $new_category = new BlogEtcCategory($request->all());
        $new_category->save();
        Helpers::flash_message("Saved new category");
        event(new CategoryAdded($new_category));
        return redirect($new_category->edit_url());
    }
    public function edit_category($categoryId){
        $category = BlogEtcCategory::findOrFail($categoryId);
        return view("blogetc_admin::categories.edit_category")->withCategory($category);
    }
    public function update_category(UpdateBlogEtcCategoryRequest $request,$categoryId){


        /** @var BlogEtcCategory $category */
        $category = BlogEtcCategory::findOrFail($categoryId);
        $category->fill($request->all());
        $category->save();

        Helpers::flash_message("Saved category changes");
        event(new CategoryEdited($category));
        return redirect($category->edit_url());
    }
    public function destroy_category(DeleteBlogEtcCategoryRequest $request,$categoryId){


        $category = BlogEtcCategory::findOrFail($categoryId);
        event(new CategoryWillBeDeleted($category));
        $category->delete();

        return view ("blogetc_admin::categories.deleted_category");

    }

}
