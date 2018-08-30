<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use WebDevEtc\BlogEtc\Interfaces\BaseRequestInterface;
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
use File;

/**
 * Class BlogEtcAdminController
 * @package WebDevEtc\BlogEtc\Controllers
 */
class BlogEtcAdminController extends Controller
{
    /**
     * If false, we check if the blog_images/ dir is writable, when uploading images
     * @var bool
     */
    protected $checked_blog_image_dir_is_writable=false;

    /**
     * BlogEtcAdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);

        if (!is_array(config("blogetc"))) {
            throw new \RuntimeException('The config/blogetc.php does not exist. Publish the vendor files for the BlogEtc package by running the php artisan publish:vendor command');
        }
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function image_destination_path()
    {
        $path = public_path('/' . config("blogetc.blog_upload_dir"));

        if (!$this->checked_blog_image_dir_is_writable) {
            if (!is_writable($path)) {
                throw new \RuntimeException("Image destination path is not writable ($path)");
            }
            $this->checked_blog_image_dir_is_writable=true;
        }

        return $path;
    }

    /**
     * View all posts
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $posts = BlogEtcPost::orderBy("posted_at", "desc")
            ->paginate(10);

        return view("blogetc_admin::index")
            ->withPosts($posts);
    }

    /**
     * Show form for creating new post
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create_post(Request $request)
    {
        return view("blogetc_admin::posts.add_post");
    }

    /**
     * Save a new post
     *
     * @param CreateBlogEtcPostRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
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

    /**
     * Show form to edit post
     *
     * @param Request $request
     * @param $blogPostId
     * @return mixed
     */
    public function edit_post(Request $request, $blogPostId)
    {
        $post = BlogEtcPost::findOrFail($blogPostId);
        return view("blogetc_admin::posts.edit_post")->withPost($post);
    }

    /**
     * Save changes to a post
     *
     * @param UpdateBlogEtcPostRequest $request
     * @param $blogPostId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function update_post(UpdateBlogEtcPostRequest $request, $blogPostId)
    {

        /** @var BlogEtcPost $post */
        $post = BlogEtcPost::findOrFail($blogPostId);
        $post->fill($request->all());

        $this->processUploadedImages($request, $post);

        $post->save();
        $post->categories()->sync($request->categories());

        Helpers::flash_message("Updated post");
        event(new BlogPostEdited($post));

        return redirect($post->edit_url());

    }

    /**
     * Delete a post
     *
     * @param DeleteBlogEtcPostRequest $request
     * @param $blogPostId
     * @return mixed
     */
    public function destroy_post(DeleteBlogEtcPostRequest $request, $blogPostId)
    {
        $post = BlogEtcPost::findOrFail($blogPostId);
        event(new BlogPostWillBeDeleted($post));

        $post->delete();

        // todo - delete the featured images?
        // At the moment it just issues a warning saying the images are still on the server.

        return view("blogetc_admin::posts.deleted_post")
            ->withDeletedPost($post);

    }

    /**
     * Process any uploaded images (for featured image)
     *
     * @param BaseRequestInterface $request
     * @param $new_blog_post
     * @throws \Exception
     */
    protected function processUploadedImages(BaseRequestInterface $request, BlogEtcPost $new_blog_post)
    {
        if (!config("blogetc.image_upload_enabled", true) ) {
            // image upload was disabled
            return;
        }

        foreach ((array)config('blogetc.image_sizes') as $size => $image_size_details) {

            if ($image_size_details['enabled'] && $photo = $request->get_image_file($size)) {
                // this image size is enabled, and
                // we have an uploaded image that we can use

                $image_filename = $this->getImageFilename($new_blog_post, $image_size_details, $photo);

                $destinationPath = $this->image_destination_path();


                $resizedImage = \Image::make($photo->getRealPath());
                $resizedImage = $resizedImage->fit($image_size_details['w'], $image_size_details['h']);
                $resizedImage->save($destinationPath . '/' . $image_filename, config("blogetc.image_quality", 80));

                event(new UploadedImage($new_blog_post, $resizedImage));

                $new_blog_post->$size = $image_filename;

            }
        }
    }

    /**
     * Get a filename (that doesn't exist) on the filesystem.
     *
     * Todo: support multiple filesystem locations.
     * Todo: move to its own file
     *
     * @param BlogEtcPost $new_blog_post
     * @param $image_size_details
     * @param $photo
     * @return string
     * @throws \RuntimeException
     */
    protected function getImageFilename(BlogEtcPost $new_blog_post, array $image_size_details, UploadedFile $photo)
    {


        $base = substr($new_blog_post->title, 0, 100);
        $wh = '-' . $image_size_details['w'] . 'x' . $image_size_details['h'];
        $ext = '.' . $photo->getClientOriginalExtension();


        $i = 0;

        while (true) {

            $suffix = $i > 1 ? '-' . str_random(5) : '';
            $attempt = str_slug($base . $suffix . $wh) . $ext;

            if (!File::exists($this->image_destination_path() . "/" . $attempt)) {
                return $attempt;
            }


            if ($i > 100) {
                throw new \RuntimeException("Unable to find a free filename after $i attempts - aborting now.");
            }

            $i++;
        }


    }


}
