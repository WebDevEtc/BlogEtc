<?php
namespace WebDevEtc\BlogEtc\Services;

use Auth;
use Exception;
use File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Image;
use Intervention\Image\Constraint;
use RuntimeException;
use WebDevEtc\BlogEtc\Events\UploadedImage;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Models\BlogEtcUploadedPhoto;
use WebDevEtc\BlogEtc\Requests\BaseBlogEtcPostRequest;

class BlogEtcUploadsService
{

    /**
     * Store new image upload meta data in database
     *
     * @param int|null $blogPostID
     * @param string $imageTitle
     * @param string $source
     * @param int|null $uploaderID
     * @param array $uploadedImages
     */
    protected function create(?int $blogPostID, string $imageTitle, string $source, ?int $uploaderID, array $uploadedImages)
    {
        // store the image upload.
        BlogEtcUploadedPhoto::create([
            'blog_etc_post_id' => $blogPostID,
            'image_title' => $imageTitle,
            'source' => $source,
            'uploader_id' => $uploaderID,
            'uploaded_images' => $uploadedImages,
        ]);
    }


    /**
     * Small method to increase memory limit.
     * This can be defined in the config file. If blogetc.memory_limit is false/null then it won't do anything.
     * This is needed though because if you upload a large image it'll not work
     */
    protected function increaseMemoryLimit(): void
    {
        // increase memory - change this setting in config file
        if (config('blogetc.memory_limit')) {
            ini_set('memory_limit', config('blogetc.memory_limit'));
        }
    }

    /**
     * Handle an image upload via the upload image section (not blog post featured image)
     *
     * @param $uploadedImage
     * @param string $imageTitle
     * @param $sizesToUpload
     * @return array
     */
    public function processUpload($uploadedImage, string $imageTitle,  $sizesToUpload )
    {
        // to save in db later
        $uploadedImageDetails = [];
        $this->increaseMemoryLimit();


        // now upload a full size - this is a special case, not in the config file. We only store full size images in
        // this class, not as part of the featured blog image uploads.
        // TODO - replace with empty()
        if (isset($sizesToUpload['blogetc_full_size']) && $sizesToUpload['blogetc_full_size'] === 'true') {
            $uploadedImageDetails['blogetc_full_size'] = $this->uploadAndResize(
                null,
                $imageTitle,
                'fullsize',
                $uploadedImage
            );
        }

        foreach ((array)config('blogetc.image_sizes') as $size => $imageSizeDetails) {
            if (!isset($sizesToUpload[$size]) || !$sizesToUpload[$size] || !$imageSizeDetails['enabled']) {
                continue;
            }

            // this image size is enabled, and
            // we have an uploaded image that we can use
            $uploadedImageDetails[$size] = $this->uploadAndResize(
                null,
                $imageTitle,
                $imageSizeDetails,
                $uploadedImage
            );
        }


        // store the image data in db:
        $this->create($imageTitle, BlogEtcUploadedPhoto::SOURCE_IMAGE_UPLOAD, Auth::id(), $uploadedImageDetails);

        return $uploadedImageDetails;
    }





    /**
     * Process any uploaded images (for featured image)
     *
     * @param BaseBlogEtcPostRequest $request
     * @param BlogEtcPost $new_blog_post
     * @todo - next full release, tidy this up!
     */
    public function processFeaturedUpload(BaseBlogEtcPostRequest $request, BlogEtcPost $new_blog_post): void
    {
        if (!config('blogetc.image_upload_enabled')) {
            // image upload was disabled
            return;
        }

        $newSizes = [];

        $this->increaseMemoryLimit();

        // to save in db later
        $uploaded_image_details = [];

        foreach ((array)config('blogetc.image_sizes') as $size => $image_size_details) {
            // TODO - add interface, or add to base request b/c get_image_file() isn't technically always be there
            if ($image_size_details['enabled'] && $photo = $request->get_image_file($size)) {
                // this image size is enabled, and
                // we have an uploaded image that we can use

                // TODO - this method does not exist
                $uploaded_image = $this->uploadAndResize(
                    $new_blog_post,
                    $new_blog_post->title,
                    $image_size_details,
                    $photo
                );

                $newSizes[$size] = $uploaded_image['filename'];

                $uploaded_image_details[$size] = $uploaded_image;
            }
        }

        // store the image upload.
        // todo: link this to the blogetc_post row.
        if (count(array_filter($uploaded_image_details)) > 0) {
            $this->create(
                $new_blog_post->id,
                $new_blog_post->title,
                BlogEtcUploadedPhoto::SOURCE_FEATURED_IMAGE,
                \Auth::id(),
                $uploaded_image_details
            );
        }
    }























    /**
     * @param BlogEtcPost $new_blog_post
     * @param $suggested_title - used to help generate the filename
     * @param $imageSizeDetails - either an array (with 'w' and 'h') or a string (and it'll be uploaded at full size,
     * no size reduction, but will use this string to generate the filename)
     * @param $photo
     * @return array
     * @throws Exception
     */
    protected function uploadAndResize(
        ?BlogEtcPost $new_blog_post,
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
        } elseif ($imageSizeDetails === 'fullsize') {
            // nothing to do here - no resizing needed.
            // We just need to set $w/$h with the original w/h values
            $w = $resizedImage->width();
            $h = $resizedImage->height();
        } else {
            throw new RuntimeException('Invalid image_size_details value of ' . $imageSizeDetails);
        }

        // save image
        $resizedImage->save($destinationPath . '/' . $image_filename, config('blogetc.image_quality', 80));

        // fire event
        event(new UploadedImage($image_filename, $resizedImage, $new_blog_post, __METHOD__));

        // return the filename and w/h details
        return [
            'filename' => $image_filename,
            'w' => $w,
            'h' => $h,
        ];
    }

    /**
     * Get a filename (that doesn't exist) on the filesystem.
     *
     * Todo: support multiple filesystem locations.
     *
     * @param string $suggested_title
     * @param $image_size_details - either an array (with w/h attributes) or a string
     * @param UploadedFile $photo
     * @return string
     * @throws RuntimeException
     */
    protected function getImageFilename(string $suggested_title, $image_size_details, UploadedFile $photo): string
    {
        $base = $this->baseFilename($suggested_title);

        // $wh will be something like "-1200x300"
        $wh = $this->getDimensions($image_size_details);
        $ext = '.' . $photo->getClientOriginalExtension();

        for ($i = 1; $i <= self::$num_of_attempts_to_find_filename; $i++) {
            // add suffix if $i>1
            $suffix = $i > 1 ? '-' . Str::random(5) : '';

            $attempt = Str::slug($base . $suffix . $wh) . $ext;

            if (!File::exists($this->imageDestinationPath() . '/' . $attempt)) {
                // filename doesn't exist, let's use it!
                return $attempt;
            }
        }

        // too many attempts...
        throw new RuntimeException("Unable to find a free filename after $i attempts - aborting now.");
    }

    /**
     * @param string $suggestedTitle
     * @return string
     */
    protected function baseFilename(string $suggestedTitle): string
    {
        $base = substr($suggestedTitle, 0, 100);
        return $base ?: 'image-' . Str::random(5);
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
     * @return string
     * @throws RuntimeException
     */
    protected function getDimensions($imageSize): string
    {
        if (is_array($imageSize)) {
            return '-' . $imageSize['w'] . 'x' . $imageSize['h'];
        }

        if (is_string($imageSize)) {
            return '-' . Str::slug(substr($imageSize, 0, 30));
        }

        // was not a string or array, so error
        throw new RuntimeException('Invalid image_size_details: must be an array with w and h, or a string');
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    protected function imageDestinationPath(): string
    {
        $path = public_path('/' . config('blogetc.blog_upload_dir'));

        $this->checkDestinationWritable($path);

        return $path;
    }

    /**
     * Check if the image destination directory is writable.
     * Throw an exception if it was not writable
     * @param $path
     * @throws RuntimeException
     */
    protected function checkDestinationWritable(string $path): void
    {
        if (!$this->checked_blog_image_dir_is_writable) {
            if (!is_writable($path)) {
                throw new RuntimeException("Image destination path is not writable ($path)");
            }
            $this->checked_blog_image_dir_is_writable = true;
        }
    }





}
