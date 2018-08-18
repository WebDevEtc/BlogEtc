<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogEtcPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_etc_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("user_id")->index()->nullable();
            $table->dateTime("posted_at")->index()->nullable()->comment("Public posted at time");
            $table->boolean("is_published")->default(true);;
            $table->string("title")->nullable()->default("New blog post");
            $table->string("subtitle")->nullable()->default("");
            $table->mediumText("post_body")->nullable();
            $table->string("use_view_file")->nullable();
            $table->text("meta_desc")->nullable();
            $table->string("slug")->unique();

            $table->string('image_large')->nullable();
            $table->string('image_medium')->nullable();
            $table->string('image_thumbnail')->nullable();

            $table->timestamps();
        });

        Schema::create('blog_etc_categories', function (Blueprint $table) {
            $table->increments('id');

            $table->string("category_name")->nullable();
            $table->string("slug")->unique();

            $table->unsignedInteger("created_by")->nullable()->index();

            $table->timestamps();
        });

        Schema::create('blog_etc_post_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("blog_etc_post_id")->index();
            $table->foreign('blog_etc_post_id')->references('id')->on('blog_etc_posts')->onDelete("cascade");
            $table->unsignedInteger("blog_etc_category_id")->index()->onDelete("cascade");
            $table->foreign('blog_etc_category_id')->references('id')->on('blog_etc_categories');
        });


        Schema::create('blog_etc_comments', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger("blog_etc_post_id")->index();
            $table->foreign('blog_etc_post_id')->references('id')->on('blog_etc_posts')->onDelete("cascade");;
            $table->unsignedInteger("user_id")->nullable()->index();

            $table->string("ip")->nullable();
            $table->string("author_name")->nullable();

            $table->text("comment");

            $table->boolean("approved")->default(true);

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog_etc_posts');
        Schema::dropIfExists('blog_etc_post_categories');
        Schema::dropIfExists('blog_etc_categories');
    }
}
