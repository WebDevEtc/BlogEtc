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
use WebDevEtc\BlogEtc\Services\BlogEtcUploadsService;
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
     * @var BlogEtcUploadsService
     */
    private $uploadsService;

    /**
     * BlogEtcAdminController constructor.
     * @param BlogEtcUploadsService $uploadsService
     */
    public function __construct(BlogEtcUploadsService $uploadsService)
    {
        $this->uploadsService = $uploadsService;

        // ensure that the logged in user has correct permission
        $this->middleware(UserCanManageBlogPosts::class);

        // ensure the config file exists
        if (!is_array(config('blogetc'))) {
            throw new RuntimeException(
                'The config/blogetc.php does not exist. ' .
                'Publish the vendor files for the BlogEtc package by running the php artisan publish:vendor command'
            );
        }

        if (!config('blogetc.image_upload_enabled') && !app()->runningInConsole()) {
            throw new RuntimeException('Image uploads in BlogEtc are disabled in the configuration');
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
        $sizeToUpload = $request->get('sizes_to_upload');

        $this->uploadsService->processUpload(
            $request,
            $request->get('image_title'),
            $sizeToUpload

        );
        // TODO check response, probably redirect it
//        $photo = $request->file('upload');


        return $uploadedImageDetails;
    }
}
