<?php

namespace WebDevEtc\BlogEtc\Traits;

use Exception;
use File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Image;
use Intervention\Image\Constraint;
use RuntimeException;
use WebDevEtc\BlogEtc\Events\UploadedImage;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;

trait UploadFileTrait
{
    /** How many tries before we throw an Exception error */
    protected static $num_of_attempts_to_find_filename = 100;

    /**
     * If false, we check if the blog_images/ dir is writable, when uploading images
     * @var bool
     */
    protected $checked_blog_image_dir_is_writable = false;

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
     * @param BlogEtcPost $new_blog_post
     * @param $suggested_title - used to help generate the filename
     * @param $imageSizeDetails - either an array (with 'w' and 'h') or a string (and it'll be uploaded at full size, no size reduction, but will use this string to generate the filename)
     * @param $photo
     * @return array
     * @throws Exception
     */
    protected function uploadAndResize(?BlogEtcPost $new_blog_post, $suggested_title, $imageSizeDetails,UploadedFile $photo): array
    {
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
