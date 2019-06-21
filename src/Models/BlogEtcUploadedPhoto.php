<?php

namespace WebDevEtc\BlogEtc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogEtcUploadedPhoto extends Model
{
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
    public function uploader():BelongsTo
    {
        return $this->belongsTo(config('blogetc.user_model'), 'uploader_id');
    }
}
