<?php

namespace WebDevEtc\BlogEtc\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Illuminate\View\View;
use RuntimeException;
use WebDevEtc\BlogEtc\Middleware\UserCanManageBlogPosts;
use WebDevEtc\BlogEtc\Models\BlogEtcUploadedPhoto;
use WebDevEtc\BlogEtc\Requests\UploadImageRequest;
use WebDevEtc\BlogEtc\Traits\UploadFileTrait;

/**
 * Class BlogEtcAdminController
 * @package WebDevEtc\BlogEtc\Controllers
 * @todo - a lot of this will be refactored. The public API won't change.
 */
class BlogEtcImageUploadController extends Controller
{
    use UploadFileTrait;

    /**
     * BlogEtcAdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);

        if (!is_array(config('blogetc'))) {
            throw new RuntimeException(
                'The config/blogetc.php does not exist.' .
                ' Publish the vendor files for the BlogEtc package by running the php artisan publish:vendor command'
            );
        }

        if (!config('blogetc.image_upload_enabled') && !app()->runningInConsole()) {
            throw new RuntimeException('The blogetc.php config option has not enabled image uploading');
        }
    }

    /**
     * Show the main listing of uploaded images
     */
    public function index(): View
    {
        return view(
            'blogetc_admin::imageupload.index',
            [
                'uploaded_photos' => BlogEtcUploadedPhoto::orderBy('id', 'desc')->paginate(10),
            ]
        );
    }

    /**
     * show the form for uploading a new image
     */
    public function create(): View
    {
        return view('blogetc_admin::imageupload.create', [
            'imageSizes' => (array)config('blogetc.image_sizes'),
        ]);
    }

    /**
     * Save a new uploaded image
     *
     * @param UploadImageRequest $request
     * @return View
     * @throws Exception
     */
    public function store(UploadImageRequest $request): View
    {
        $processed_images = $this->processUploadedImages($request);

        return view('blogetc_admin::imageupload.uploaded', ['images' => $processed_images]);
    }

    /**
     * Process any uploaded images (for featured image)
     *
     * @param UploadImageRequest $request
     *
     * @return array returns an array of details about each file resized.
     * @throws Exception
     * @todo - This class was added after the other main features, so this duplicates some code from the main blog post
     *         admin controller (BlogEtcAdminController). For next full release this should be tided up.
     */
    protected function processUploadedImages(UploadImageRequest $request): array
    {
        $this->increaseMemoryLimit();
        $photo = $request->file('upload');

        // to save in db later
        $uploadedImageDetails = [];

        $sizeToUpload = $request->get('sizes_to_upload');

        // now upload a full size - this is a special case, not in the config file. We only store full size images in
        // this class, not as part of the featured blog image uploads.
        if (isset($sizeToUpload['blogetc_full_size']) && $sizeToUpload['blogetc_full_size'] === 'true') {
            $uploadedImageDetails['blogetc_full_size'] = $this->uploadAndResize(
                null,
                $request->get('image_title'),
                'fullsize',
                $photo
            );
        }

        foreach ((array)config('blogetc.image_sizes') as $size => $imageSizeDetails) {
            if (!isset($sizeToUpload[$size]) || !$sizeToUpload[$size] || !$imageSizeDetails['enabled']) {
                continue;
            }

            // this image size is enabled, and
            // we have an uploaded image that we can use
            $uploadedImageDetails[$size] = $this->uploadAndResize(
                null,
                $request->get('image_title'),
                $imageSizeDetails,
                $photo
            );
        }

        // store the image upload.
        BlogEtcUploadedPhoto::create([
            'image_title' => $request->get('image_title'),
            'source' => 'ImageUpload',
            'uploader_id' => optional(Auth::user())->id,
            'uploaded_images' => $uploadedImageDetails,
        ]);

        return $uploadedImageDetails;
    }
}
