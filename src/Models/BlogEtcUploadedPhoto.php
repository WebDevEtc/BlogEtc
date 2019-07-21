<?php

namespace WebDevEtc\BlogEtc\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BlogEtcUploadedPhoto
 * @property BlogEtcPost blogPost
 * @property User|\App\Models\User uploader
 * @property int|null blog_etc_post_id
 * @property string image_title
 * @property ing|null uploader_id
 * @property string source - see const values in this model
 * @property uploaded_images
 *
 * @package WebDevEtc\BlogEtc\Models
 */
class BlogEtcUploadedPhoto extends Model
{
    public const SOURCE_IMAGE_UPLOAD = 'ImageUpload';
    public const SOURCE_FEATURED_IMAGE = 'BlogFeaturedImage';
    /**
     * DB table name
     *
     * @var string
     */
    public $table = 'blog_etc_uploaded_photos';

    /**
     * Eloquent cast attributes
     *
     * @var array
     */
    public $casts = [
        'uploaded_images' => 'array',
    ];

    /**
     * Fillable attributes
     *
     * @var array
     */
    public $fillable = [
        'blog_etc_post_id',
        'image_title',
        'uploader_id',
        'source',
        'uploaded_images',
    ];

    /**
     * Relationship for the user
     *
     * @return BelongsTo
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(config('blogetc.user_model'), 'uploader_id');
    }

    /**
     * Relationship for a blog post for which this image is a featured image.
     *
     * (Only set when initially uploading a featured image, does not mean that this image is still used as a featured
     * image - this is just for convenience rather than something to rely upon).
     *
     * @return BelongsTo
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogEtcPost::class);
    }
}
