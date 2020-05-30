<?php

namespace WebDevEtc\BlogEtc\Services;

use Auth;
use Carbon\Carbon;
use Exception;
use File;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Image;
use Intervention\Image\Constraint;
use RuntimeException;
use Storage;
use WebDevEtc\BlogEtc\Events\BlogPostAdded;
use WebDevEtc\BlogEtc\Events\BlogPostEdited;
use WebDevEtc\BlogEtc\Events\BlogPostWillBeDeleted;
use WebDevEtc\BlogEtc\Events\UploadedImage;
use WebDevEtc\BlogEtc\Helpers;
use WebDevEtc\BlogEtc\Interfaces\LegacyGetImageFileInterface;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Models\UploadedPhoto;
use WebDevEtc\BlogEtc\Repositories\UploadedPhotosRepository;
use WebDevEtc\BlogEtc\Requests\CreateBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\DeleteBlogEtcPostRequest;
//use WebDevEtc\BlogEtc\Requests\PostRequest;
use WebDevEtc\BlogEtc\Requests\UpdateBlogEtcPostRequest;
use WebDevEtc\BlogEtc\Requests\UploadImageRequest;

/**
 * Class UploadsService.
 */
class UploadsService
{
    /**
     * How many iterations to find an available filename, before exception.
     *
     * @var int
     */
    private static $availableFilenameAttempts = 10;
    /**
     * @var UploadedPhotosRepository
     */
    private $repository;

    public function __construct(UploadedPhotosRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Save a new post.
     */
    public function legacyStorePost(CreateBlogEtcPostRequest $request)
    {
        $new_blog_post = new Post($request->all());

        $this->legacyProcessUploadedImages($request, $new_blog_post);

        if (! $new_blog_post->posted_at) {
            $new_blog_post->posted_at = Carbon::now();
        }

        $new_blog_post->user_id = Auth::user()->id;
        $new_blog_post->save();

        $new_blog_post->categories()->sync($request->categories());

        Helpers::flashMessage('Added post');
        event(new BlogPostAdded($new_blog_post));

        return $new_blog_post->editUrl();
    }

    /**
     * This uses some legacy code. This will get refactored soon into something nicer.
     */
    public function legacyUpdatePost(UpdateBlogEtcPostRequest $request, $blogPostId)
    {
        /** @var Post $post */
        $post = Post::findOrFail($blogPostId);
        $post->fill($request->all());

        $this->legacyProcessUploadedImages($request, $post);

        $post->save();
        $post->categories()->sync($request->categories());

        Helpers::flashMessage('Updated post');
        event(new BlogPostEdited($post));

        return $post->editUrl();
    }

    /**
     * Legacy method - will get updated soon.
     *
     * Process any uploaded images (for featured image).
     *
     * @throws Exception
     *
     * @return array returns an array of details about each file resized
     *
     * @todo - This class was added after the other main features, so this duplicates some code from the main blog post admin controller (BlogEtcAdminController). For next full release this should be tided up.
     */
    public function legacyProcessUploadedImagesSingle(UploadImageRequest $request)
    {
        $this->increaseMemoryLimit();
        $photo = $request->file('upload');

        $uploaded_image_details = [];

        $sizes_to_upload = $request->get('sizes_to_upload');

        // now upload a full size - this is a special case, not in the config file. We only store full size images in this class, not as part of the featured blog image uploads.
        if (isset($sizes_to_upload['blogetc_full_size']) && 'true' === $sizes_to_upload['blogetc_full_size']) {
            $uploaded_image_details['blogetc_full_size'] = $this->legacyUploadAndResize(null, $request->get('image_title'),
                'fullsize', $photo);
        }

        foreach ((array) config('blogetc.image_sizes') as $size => $image_size_details) {
            if (! isset($sizes_to_upload[$size]) || ! $sizes_to_upload[$size] || ! $image_size_details['enabled']) {
                continue;
            }

            $uploaded_image_details[$size] = $this->legacyUploadAndResize(null, $request->get('image_title'),
                $image_size_details, $photo);
        }

        UploadedPhoto::create([
            'image_title'     => $request->get('image_title'),
            'source'          => 'ImageUpload',
            'uploader_id'     => (int) Auth::id(),
            'uploaded_images' => $uploaded_image_details,
        ]);

        return $uploaded_image_details;
    }

    /**
     * Process any uploaded images (for featured image).
     *
     * @param $new_blog_post
     *
     * @throws Exception
     *
     * @todo - next full release, tidy this up!
     */
    public function legacyProcessUploadedImages(LegacyGetImageFileInterface $request, Post $new_blog_post)
    {
        if (! config('blogetc.image_upload_enabled')) {
            return;
        }

        $this->increaseMemoryLimit();

        $uploaded_image_details = [];

        foreach ((array) config('blogetc.image_sizes') as $size => $image_size_details) {
            if ($image_size_details['enabled'] && $photo = $request->get_image_file($size)) {
                $uploaded_image = $this->legacyUploadAndResize($new_blog_post, $new_blog_post->title, $image_size_details,
                    $photo);

                $new_blog_post->$size = $uploaded_image['filename'];
                $uploaded_image_details[$size] = $uploaded_image;
            }
        }

        // todo: link this to the blogetc_post row.
        if (count(array_filter($uploaded_image_details)) > 0) {
            UploadedPhoto::create([
                'source'          => 'BlogFeaturedImage',
                'uploaded_images' => $uploaded_image_details,
            ]);
        }
    }

    /**
     * @param Post $new_blog_post
     * @param $suggested_title string - used to help generate the filename
     * @param $image_size_details mixed - either an array (with 'w' and 'h') or a string (and it'll be uploaded at full size, no size reduction, but will use this string to generate the filename)
     * @param $photo
     *
     * @throws Exception
     *
     * @return array
     */
    protected function legacyUploadAndResize(Post $new_blog_post = null, $suggested_title, $image_size_details, $photo)
    {
        $image_filename = $this->legacyGetImageFilename($suggested_title, $image_size_details, $photo);
        $destinationPath = $this->image_destination_path();

        $resizedImage = Image::make($photo->getRealPath());

        if (is_array($image_size_details)) {
            $w = $image_size_details['w'];
            $h = $image_size_details['h'];

            if (isset($image_size_details['crop']) && $image_size_details['crop']) {
                $resizedImage = $resizedImage->fit($w, $h);
            } else {
                $resizedImage = $resizedImage->resize($w, $h, static function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
        } elseif ('fullsize' === $image_size_details) {
            $w = $resizedImage->width();
            $h = $resizedImage->height();
        } else {
            throw new Exception('Invalid image_size_details value');
        }

        $resizedImage->save($destinationPath.'/'.$image_filename, config('blogetc.image_quality', 80));

        event(new UploadedImage($image_filename, $resizedImage, $new_blog_post, __METHOD__));

        return [
            'filename' => $image_filename,
            'w'        => $w,
            'h'        => $h,
        ];
    }

    /**
     * @throws RuntimeException
     *
     * @return string
     */
    public function image_destination_path()
    {
        $path = public_path('/'.config('blogetc.blog_upload_dir'));
        $this->check_image_destination_path_is_writable($path);

        return $path;
    }

    /**
     * Legacy - will be removed
     * Check if the image destination directory is writable.
     * Throw an exception if it was not writable.
     *
     * @param $path
     *
     * @throws RuntimeException
     */
    protected function check_image_destination_path_is_writable($path)
    {
        if (! is_writable($path)) {
            throw new RuntimeException("Image destination path is not writable ($path)");
        }
    }

    /**
     * Legacy function, will get refactored soon into something nicer!
     * Get a filename (that doesn't exist) on the filesystem.
     *
     * Todo: support multiple filesystem locations.
     *
     * @param $image_size_details - either an array (with w/h attributes) or a string
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function legacyGetImageFilename(string $suggested_title, $image_size_details, UploadedFile $photo)
    {
        $base = $this->generate_base_filename($suggested_title);

        // $wh will be something like "-1200x300"
        $wh = $this->getDimensions($image_size_details);
        $ext = '.'.$photo->getClientOriginalExtension();

        for ($i = 1; $i <= 10; ++$i) {
            // add suffix if $i>1
            $suffix = $i > 1 ? '-'.str_random(5) : '';

            $attempt = str_slug($base.$suffix.$wh).$ext;

            if (! File::exists($this->image_destination_path().'/'.$attempt)) {
                // filename doesn't exist, let's use it!
                return $attempt;
            }
        }

        // too many attempts...
        throw new RuntimeException("Unable to find a free filename after $i attempts - aborting now.");
    }

    /**
     * @return string
     */
    protected function generate_base_filename(string $suggested_title)
    {
        $base = substr($suggested_title, 0, 100);
        if (! $base) {
            // if we have an empty string then we should use a random one:
            $base = 'image-'.str_random(5);

            return $base;
        }

        return $base;
    }

    /**
     * Given a filename, return a public url for that asset on the filesystem as defined in the config.
     */
    public static function publicUrl(string $filename): string
    {
        return self::disk()->url(config('blogetc.blog_upload_dir').'/'.$filename);
    }

    /**
     * Disk for filesystem storage.
     *
     * Set the relevant config file to use things such as S3.
     */
    public static function disk(): Filesystem
    {
        return Storage::disk(config('blogetc.image_upload_disk', 'public'));
    }

//    /**
//     * Handle an image upload via the upload image section (not blog post featured image).
//     *
//     * @param $uploadedImage
//     *
//     * @throws Exception
//     */
//    public function processUpload($uploadedImage, string $imageTitle): array
//    {
//        // to save in db later
//        $uploadedImageDetails = [];
//        $this->increaseMemoryLimit();
//
//        if (config('blogetc.image_store_full_size')) {
//            // Store as full size
//            $uploadedImageDetails['blogetc_full_size'] = $this->uploadAndResize(
//                null,
//                $imageTitle,
//                'fullsize',
//                $uploadedImage
//            );
//        }
//
//        foreach ((array) config('blogetc.image_sizes') as $size => $imageSizeDetails) {
//            $uploadedImageDetails[$size] = $this->uploadAndResize(
//                null,
//                $imageTitle,
//                $imageSizeDetails,
//                $uploadedImage
//            );
//        }
//
//        // Store the image data in db:
//        $this->storeInDatabase(
//            null,
//            $imageTitle,
//            UploadedPhoto::SOURCE_IMAGE_UPLOAD,
//            (int) Auth::id(),
//            $uploadedImageDetails
//        );
//
//        return $uploadedImageDetails;
//    }

    /**
     * Small method to increase memory limit.
     * This can be defined in the config file. If blogetc.memory_limit is false/null then it won't do anything.
     * This is needed though because if you upload a large image it'll not work.
     */
    public function increaseMemoryLimit(): void
    {
        // increase memory - change this setting in config file
        if (config('blogetc.memory_limit')) {
            ini_set('memory_limit', config('blogetc.memory_limit'));
        }
    }

    /**
     * Resize and store an image.
     *
     * @param Post $new_blog_post
     * @param $suggested_title - used to help generate the filename
     * @param array|string $imageSizeDetails - either an array (with 'w' and 'h') or a string (and it'll be uploaded at full size,
     *                                       no size reduction, but will use this string to generate the filename)
     * @param $photo
     *
     * @throws Exception
     */
    protected function uploadAndResize(
        ?Post $new_blog_post,
        $suggested_title,
        $imageSizeDetails,
        UploadedFile $photo
    ): array {
        // get the filename/filepath
        $image_filename = $this->getImageFilename($suggested_title, $imageSizeDetails, $photo);
        $destinationPath = $this->imageDestinationPath();

        // make image
        $resizedImage = Image::make($photo->getRealPath());

        if (is_array($imageSizeDetails)) {
            // resize to these dimensions:
            $w = $imageSizeDetails['w'];
            $h = $imageSizeDetails['h'];

            if (isset($imageSizeDetails['crop']) && $imageSizeDetails['crop']) {
                $resizedImage = $resizedImage->fit($w, $h);
            } else {
                $resizedImage = $resizedImage->resize($w, $h, static function (Constraint $constraint) {
                    $constraint->aspectRatio();
                });
            }
        } elseif ('fullsize' === $imageSizeDetails) {
            // nothing to do here - no resizing needed.
            // We just need to set $w/$h with the original w/h values
            $w = $resizedImage->width();
            $h = $resizedImage->height();
        } else {
            throw new RuntimeException('Invalid image_size_details value of '.$imageSizeDetails);
        }

        $imageQuality = config('blogetc.image_quality', 80);
        $format = pathinfo($image_filename, PATHINFO_EXTENSION);
        $resizedImageData = $resizedImage->encode($format, $imageQuality);
        $this::disk()->put($destinationPath.'/'.$image_filename, $resizedImageData);

        event(new UploadedImage($image_filename, $resizedImage, $new_blog_post, __METHOD__));

        return [
            'filename' => $image_filename,
            'w'        => $w,
            'h'        => $h,
        ];
    }

    /**
     * Get a filename (that doesn't exist) on the filesystem.
     *
     * @param $image_size_details - either an array (with w/h attributes) or a string
     *
     * @throws RuntimeException
     */
    protected function getImageFilename(string $suggested_title, $image_size_details, UploadedFile $photo): string
    {
        $base = $this->baseFilename($suggested_title);

        // $wh will be something like "-1200x300"
        $wh = $this->getDimensions($image_size_details);
        $ext = '.'.$photo->getClientOriginalExtension();

        for ($i = 1; $i <= self::$availableFilenameAttempts; ++$i) {
            // add suffix if $i>1
            $suffix = $i > 1 ? '-'.Str::random(5) : '';

            $attempt = Str::slug($base.$suffix.$wh).$ext;

            if (! $this::disk()->exists($this->imageDestinationPath().'/'.$attempt)) {
                return $attempt;
            }
        }

        // too many attempts...
        throw new RuntimeException("Unable to find a free filename after $i attempts - aborting now.");
    }

    protected function baseFilename(string $suggestedTitle): string
    {
        $base = substr($suggestedTitle, 0, 100);

        return $base ?: 'image-'.Str::random(5);
    }

    /**
     * Get the width and height as a string, with x between them
     * (123x456).
     *
     * It will always be prepended with '-'
     *
     * Example return value: -123x456
     *
     * $image_size_details should either be an array with two items ([$width, $height]),
     * or a string.
     *
     * If an array is given:
     * getWhForFilename([123,456]) it will return "-123x456"
     *
     * If a string is given:
     * getWhForFilename("some string") it will return -some-string". (max len: 30)
     *
     * @param array|string $imageSize
     *
     * @throws RuntimeException
     */
    protected function getDimensions($imageSize): string
    {
        if (is_array($imageSize)) {
            return '-'.$imageSize['w'].'x'.$imageSize['h'];
        }

        return '-'.Str::slug(substr($imageSize, 0, 30));
    }

    /**
     * @deprecated - use getDimensions()
     */
    protected function getWhForFilename($image_size_details)
    {
        return $this->getDimensions($image_size_details);
    }

    /**
     * @throws RuntimeException
     */
    protected function imageDestinationPath(): string
    {
        return config('blogetc.blog_upload_dir');
    }

    /**
     * Store new image upload meta data in database.
     */
    protected function storeInDatabase(
        ?int $blogPostID,
        string $imageTitle,
        string $source,
        ?int $uploaderID,
        array $uploadedImages
    ): UploadedPhoto {
        // store the image upload.
        return $this->repository->create([
            'blog_etc_post_id' => $blogPostID,
            'image_title'      => $imageTitle,
            'source'           => $source,
            'uploader_id'      => $uploaderID,
            'uploaded_images'  => $uploadedImages,
        ]);
    }

//    /**
//     * Process any uploaded images (for featured image).
//     *
//     * @throws Exception
//     *
//     * @todo - next full release, tidy this up!
//     */
//    public function processFeaturedUpload(PostRequest $request, Post $new_blog_post): ?array
//    {
//        if (! config('blogetc.image_upload_enabled')) {
//            // image upload was disabled
//            return null;
//        }
//
//        $newSizes = [];
//        $this->increaseMemoryLimit();
//
//        // to save in db later
//        $uploaded_image_details = [];
//
//        $enabledImageSizes = collect((array) config('blogetc.image_sizes'))
//            ->filter(function ($size) {
//                return ! empty($size['enabled']);
//            });
//
//        foreach ($enabledImageSizes as $size => $image_size_details) {
//            $photo = $request->getImageSize($size);
//
//            if (! $photo) {
//                continue;
//            }
//
//            $uploaded_image = $this->uploadAndResize(
//                $new_blog_post,
//                $new_blog_post->title,
//                $image_size_details,
//                $photo
//            );
//
//            $newSizes[$size] = $uploaded_image['filename'];
//
//            $uploaded_image_details[$size] = $uploaded_image;
//        }
//
//        // store the image upload.
//        if (empty($newSizes)) {
//            // Nothing to do if there were no sizes in config.
//            return null;
//        }
//
//        // todo: link this to the blogetc_post row.
//        $this->storeInDatabase(
//            $new_blog_post->id,
//            $new_blog_post->title,
//            UploadedPhoto::SOURCE_FEATURED_IMAGE,
//            Auth::id(),
//            $uploaded_image_details
//        );
//
//        return $newSizes;
//    }

    /**
     * Legacy function, will be refactored soon.
     *
     * @param $blogPostId
     *
     * @return mixed
     */
    public function legacyDestroyPost(/* @scrutinizer ignore-unused */ DeleteBlogEtcPostRequest $request, $blogPostId)
    {
        $post = Post::findOrFail($blogPostId);
        event(new BlogPostWillBeDeleted($post));

        $post->delete();

        // todo - delete the featured images?
        // At the moment it just issues a warning saying the images are still on the server.

        return $post;
    }
}
