<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUploadedPhotoPostIDForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog_etc_uploaded_photos', function (Blueprint $table) {
            $table->dropForeign('uploaded_photo_post_id_fk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blog_etc_uploaded_photos', function (Blueprint $table) {
            // Not adding FK back as it can cause some constraint issues and honestly, who ever runs migrate:rollback
            // apart from during in development!
        });
    }
}
