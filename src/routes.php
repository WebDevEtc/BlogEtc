<?php

Route::group(['middleware' => ['web'], 'namespace' => '\WebDevEtc\BlogEtc\Controllers'], static function () {
    /* The main public facing blog routes - show all posts, view a category, rss feed, view a single post, also the add comment route */
    Route::group(['prefix' => config('blogetc.blog_prefix', 'blog')], static function () {
        Route::get('/', 'PostsController@index')->name('blogetc.index');

        Route::get('/search', 'PostsController@search')->name('blogetc.search');

        Route::get('/feed', 'BlogEtcRssFeedController@feed')->name('blogetc.feed');

        Route::get('/category/{categorySlug}', 'PostsController@showCategory')->name('blogetc.view_category');

        Route::get('/{blogPostSlug}', 'PostsController@show')->name('blogetc.single');

        Route::group(['middleware' => 'throttle:10,3'], static function () {
            Route::post('save_comment/{blogPostSlug}', 'CommentsController@store')->name('blogetc.comments.add_new_comment');
        });
    });

    /* Admin backend routes - CRUD for posts, categories, and approving/deleting submitted comments */
    Route::group(['prefix' => config('blogetc.admin_prefix', 'blog_admin')], static function () {
        Route::get('/', 'Admin\ManagePostsController@index')->name('blogetc.admin.index');

        Route::get('/add_post', 'Admin\ManagePostsController@create')->name('blogetc.admin.create_post');

        Route::post('/add_post', 'Admin\ManagePostsController@store')->name('blogetc.admin.store_post');

        Route::get('/edit_post/{blogPostId}', 'Admin\ManagePostsController@edit')->name('blogetc.admin.edit_post');

        Route::patch('/edit_post/{blogPostId}', 'Admin\ManagePostsController@update')->name('blogetc.admin.update_post');

        Route::group(['prefix' => 'image_uploads'], static function () {
            Route::get('/', 'BlogEtcImageUploadController@index')->name('blogetc.admin.images.all');

            Route::get('/upload', 'BlogEtcImageUploadController@create')->name('blogetc.admin.images.upload');

            Route::post('/upload', 'BlogEtcImageUploadController@store')->name('blogetc.admin.images.store');
        });

        Route::delete('/delete_post/{blogPostId}', 'Admin\ManagePostsController@destroy')->name('blogetc.admin.destroy_post');

        Route::group(['prefix' => 'comments'], static function () {
            Route::get('/', 'Admin\ManageCommentsController@index')->name('blogetc.admin.comments.index');

            Route::patch('/{commentId}', 'Admin\ManageCommentsController@approve')->name('blogetc.admin.comments.approve');

            Route::delete('/{commentId}', 'Admin\ManageCommentsController@destroy')->name('blogetc.admin.comments.delete');
        });

        Route::group(['prefix' => 'categories'], static function () {
            Route::get('/', 'Admin\ManageCategoriesController@index')->name('blogetc.admin.categories.index');

            Route::get('/add_category', 'Admin\ManageCategoriesController@create')->name('blogetc.admin.categories.create_category');

            Route::post('/add_category', 'Admin\ManageCategoriesController@store')->name('blogetc.admin.categories.store_category');

            Route::get('/edit_category/{categoryId}', 'Admin\ManageCategoriesController@edit')->name('blogetc.admin.categories.edit_category');

            Route::patch('/edit_category/{categoryId}', 'Admin\ManageCategoriesController@update')->name('blogetc.admin.categories.update_category');

            Route::delete('/delete_category/{categoryId}', 'Admin\ManageCategoriesController@destroy')->name('blogetc.admin.categories.destroy_category');
        });
    });
});
