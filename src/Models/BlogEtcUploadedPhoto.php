<?php

namespace WebDevEtc\BlogEtc\Models;

use Illuminate\Database\Eloquent\Model;

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
}
