<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use WebDevEtc\BlogEtc\BaseRequestInterface;
use WebDevEtc\BlogEtc\Events\BlogPostAdded;
use WebDevEtc\BlogEtc\Events\BlogPostEdited;
use WebDevEtc\BlogEtc\Events\BlogPostWillBeDeleted;
use WebDevEtc\BlogEtc\Events\UploadedImage;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Requests\CreateBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\DeleteBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcPostRequest;

class BlogEtcAdminController extends Controller
{


    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
    }

    public function index(Request $request)
    {
        return view("blogetc_admin::index")->withPosts(BlogEtcPost::orderBy("posted_at","desc")->paginate(10));
    }

    public function create_post()
    {
        return view("blogetc_admin::posts.add_post");
    }

    public function store_post(CreateBlogEtcPostRequest $request)
    {
        $new_blog_post = new BlogEtcPost($request->all());

        $this->processUploadedImages($request, $new_blog_post);

        if (!$new_blog_post->posted_at) {
            $new_blog_post->posted_at = Carbon::now();
        }

        $new_blog_post->user_id = \Auth::user()->id;
        $new_blog_post->save();

        $new_blog_post->categories()->sync($request->categories());

        Helpers::flash_message("Added post");
        event(new BlogPostAdded($new_blog_post));
        return redirect($new_blog_post->edit_url());
    }

    public function edit_post($blogPostId)
    {
        $post = BlogEtcPost::findOrFail($blogPostId);
        return view("blogetc_admin::posts.edit_post")->withPost($post);
    }

    public function update_post(UpdateBlogEtcPostRequest $request, $blogPostId)
    {

        $post = BlogEtcPost::findOrFail($blogPostId);
        $post->fill($request->all());

        $this->processUploadedImages($request, $post);

        $post->save();
        $post->categories()->sync($request->categories());
        Helpers::flash_message("Updated post");
        event(new BlogPostEdited($post));
        return redirect($post->edit_url());

    }

    public function destroy_post(DeleteBlogEtcPostRequest $request, $blogPostId)
    {

        $post = BlogEtcPost::findOrFail($blogPostId);
        event(new BlogPostWillBeDeleted($post));
        $post->delete();

        return view("blogetc_admin::posts.deleted_post")->withDeletedPost($post);

    }

    /**
     * @param CreateBlogEtcPostRequest $request
     * @param $new_blog_post
     * @throws \Exception
     */
    protected function processUploadedImages(BaseRequestInterface $request, BlogEtcPost $new_blog_post)
    {
        if (config("blogetc.image_upload_enabled", true) == false) {
            // image upload was disabled
            return;
        }

        foreach (config('blogetc.image_sizes') as $size => $image_detail) {

            if ($image_detail['enabled']) {
                if ($photo = $request->get_image_file($size)) {

                    $imagename = str_slug($new_blog_post->title) . "-" . str_random(4) . $image_detail['w'] . "x" . $image_detail['h'] . '.' . $photo->getClientOriginalExtension();

                    $destinationPath = public_path('/' . config("blogetc.blog_upload_dir"));

                    if (!is_writable($destinationPath)) {
                        throw new \Exception("Unable to write to that directory (" . $destinationPath . ")");
                    }

                    $thumb_img = \Image::make($photo->getRealPath());
                    $thumb_img = $thumb_img->fit($image_detail['w'], $image_detail['h']);
                    $thumb_img->save($destinationPath . '/' . $imagename, config("blogetc.image_quality",80));

                    event(new UploadedImage($new_blog_post, $thumb_img));

                    $new_blog_post->$size = $imagename;
                }
            } else {
                $new_blog_post->$size = null; // set to null as it is disabled
            }
        }
    }


}
