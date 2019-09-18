<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUploadedPhotoPostIDForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog_etc_uploaded_photos', function(Blueprint $table) {
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
        Schema::table('blog_etc_uploaded_photos', function(Blueprint $table) {
            // Not adding FK back as it can cause some constraint issues and honestly, who ever runs migrate:rollback
            // apart from during in development!
        });
    }
}
