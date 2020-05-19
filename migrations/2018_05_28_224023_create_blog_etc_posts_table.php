<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateBlogEtcPostsTable.
 */
class CreateBlogEtcPostsTable extends Migration
{
    /**
     * Initial DB table setup for blog etc package.
     */
    public function up(): void
    {
        Schema::create('blog_etc_posts', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index()->nullable();
            $table->string('slug')->unique();

            $table->string('title')->nullable()->default('New blog post');
            $table->string('subtitle')->nullable()->default('');
            $table->text('meta_desc')->nullable();
            $table->mediumText('post_body')->nullable();

            $table->string('use_view_file')
                ->nullable()
                ->comment('If not null, this should refer to a blade file in /views/');

            $table->dateTime('posted_at')->index()->nullable()
                ->comment('Public posted at time, if this is in future then it wont appear yet');
            $table->boolean('is_published')->default(true);

            $table->string('image_large')->nullable();
            $table->string('image_medium')->nullable();
            $table->string('image_thumbnail')->nullable();

            $table->timestamps();
        });

        Schema::create('blog_etc_categories', static function (Blueprint $table) {
            $table->increments('id');

            $table->string('category_name')->nullable();
            $table->string('slug')->unique();
            $table->mediumText('category_description')->nullable();

            $table->unsignedInteger('created_by')->nullable()->index()->comment('user id');

            $table->timestamps();
        });

        // linking table:
        Schema::create('blog_etc_post_categories', static function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('blog_etc_post_id')->index();
            $table->foreign('blog_etc_post_id')->references('id')->on('blog_etc_posts')->onDelete('cascade');

            $table->unsignedInteger('blog_etc_category_id')->index();
            $table->foreign('blog_etc_category_id')->references('id')->on('blog_etc_categories')->onDelete('cascade');
        });

        Schema::create('blog_etc_comments', static function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('blog_etc_post_id')->index();
            $table->foreign('blog_etc_post_id')->references('id')->on('blog_etc_posts')->onDelete('cascade');
            $table->unsignedInteger('user_id')->nullable()->index()->comment('if user was logged in');

            $table->string('ip')->nullable()->comment('if enabled in the config file');
            $table->string('author_name')->nullable()->comment('if not logged in');

            $table->text('comment')->comment('the comment body');

            $table->boolean('approved')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_etc_comments');
        Schema::dropIfExists('blog_etc_post_categories');
        Schema::dropIfExists('blog_etc_categories');
        Schema::dropIfExists('blog_etc_posts');
    }
}
