<?php

// BlogEtc default routes

Route::group(['middleware' => ['web'], 'namespace' => '\WebDevEtc\BlogEtc\Controllers'], static function () {

    /**
     * The main public facing blog routes - show all posts, view a category, rss feed, view a single post, also the
     * add comment route
     */
    Route::group(
        ['prefix' => config('blogetc.blog_prefix', 'blog')],
        static function () {
            // Public blog index:
            Route::get('/', 'BlogEtcReaderController@index')
                ->name('blogetc.index');

            // Public search results:
            Route::get('/search', 'BlogEtcReaderController@search')
                ->name('blogetc.search');

            // Public RSS feed:
            Route::get('/feed', 'BlogEtcRssFeedController@feed')
                ->name('blogetc.feed'); //RSS feed

            // Public show category
            Route::get(
                '/category/{categorySlug}',
                'BlogEtcReaderController@showCategory'
            )
                ->name('blogetc.view_category');

            // Public show single blog post
            Route::get(
                '/{blogPostSlug}',
                'BlogEtcReaderController@show'
            )
                ->name('blogetc.show');

            // Public save new blog comment (throttle to a max of 10 attempts in 3 minutes):
            Route::group(['middleware' => 'throttle:10,3'], static function () {
                Route::post(
                    'save_comment/{blogPostSlug}',
                    'BlogEtcCommentWriterController@addNewComment'
                )->name('blogetc.comments.add_new_comment');
            });
        }
    );

    /* Admin backend routes - CRUD for posts, categories, and approving/deleting submitted comments */
    Route::group(['prefix' => config('blogetc.admin_prefix', 'blog_admin')], static function () {

        // Manage blog posts (admin panel)
        Route::group(['prefix' => 'posts',], static function () {
            // Show all blog posts:
            Route::get(
                '/',
                'BlogEtcAdminController@index'
            )->name('blogetc.admin.index');

            // Create a new blog post (form):
            Route::get(
                '/add_post',
                'BlogEtcAdminController@create'
            )->name('blogetc.admin.create_post');

            // Store a new blog post entry:
            Route::post(
                '/add_post',
                'BlogEtcAdminController@store'
            )->name('blogetc.admin.store_post');

            // Show the edit form:
            Route::get(
                '/edit_post/{blogPostId}',
                'BlogEtcAdminController@edit'
            )->name('blogetc.admin.edit_post');

            // Store the changes to a blog post in DB:
            Route::patch(
                '/edit_post/{blogPostID}',
                'BlogEtcAdminController@update'
            )->name('blogetc.admin.update_post');

            // Delete a blog post:
            Route::delete(
                '/delete_post/{blogPostId}',
                'BlogEtcAdminController@destroy'
            )->name('blogetc.admin.destroy_post');
        });

        // Manage Image Uploads (Admin panel)
        Route::group(['prefix' => 'image_uploads',], static function () {
            // show all uploaded images:
            Route::get(
                '/',
                'BlogEtcImageUploadController@index'
            )->name('blogetc.admin.images.all');

            // upload new image (form):
            Route::get(
                '/upload',
                'BlogEtcImageUploadController@create'
            )->name('blogetc.admin.images.upload');

            // store a new uploaded image:
            Route::post(
                '/upload',
                'BlogEtcImageUploadController@store'
            )->name('blogetc.admin.images.store');
        });


        // Manage comments (Admin Panel)
        Route::group(['prefix' => 'comments',], static function () {
            // show all comments:
            Route::get(
                '/',
                'BlogEtcCommentsAdminController@index'
            )->name('blogetc.admin.comments.index');

            // approve a comment:
            Route::patch(
                '/{commentId}',
                'BlogEtcCommentsAdminController@approve'
            )->name('blogetc.admin.comments.approve');

            // delete a comment:
            Route::delete(
                '/{commentId}',
                'BlogEtcCommentsAdminController@destroy'
            )->name('blogetc.admin.comments.delete');
        });

        // Category Admin panel - manage categories
        Route::group(['prefix' => 'categories'], static function () {
            // show all categories:
            Route::get(
                '/',
                'BlogEtcCategoryAdminController@index'
            )->name('blogetc.admin.categories.index');

            // create a new category (form):
            Route::get(
                '/add_category',
                'BlogEtcCategoryAdminController@create'
            )->name('blogetc.admin.categories.create_category');

            // store a new category in DB:
            Route::post(
                '/add_category',
                'BlogEtcCategoryAdminController@store'
            )->name('blogetc.admin.categories.store_category');

            // edit a category (form):
            Route::get(
                '/edit_category/{categoryId}',
                'BlogEtcCategoryAdminController@edit'
            )->name('blogetc.admin.categories.edit_category');

            // update a category:
            Route::patch(
                '/edit_category/{categoryId}',
                'BlogEtcCategoryAdminController@update'
            )->name('blogetc.admin.categories.update_category');

            // delete a category:
            Route::delete(
                '/delete_category/{categoryId}',
                'BlogEtcCategoryAdminController@destroy'
            )->name('blogetc.admin.categories.destroy_category');
        });
    });
});
