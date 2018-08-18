<?php

Route::group( [ 'middleware' => [ 'web' ] ] , function () {



    /** The main public facing blog routes - show all posts, view a category, rss feed, view a single post, also the add comment route */
    Route::group( [ 'prefix' => config( "blogetc.blog_prefix" , "blog" ) ] , function () {

        Route::get( "/" , '\WebDevEtc\BlogEtc\Controllers\BlogEtcReaderController@index' )->name( "blogetc.index" );
        Route::get( "/feed" , '\WebDevEtc\BlogEtc\Controllers\BlogEtcReaderController@feed' )->name( "blogetc.feed" ); //RSS feed
        Route::get( "/category/{categorySlug}" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcReaderController@view_category' )->name( "blogetc.view_category" );
        Route::get( "/{blogPostSlug}" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcReaderController@viewSinglePost' )->name( "blogetc.single" );

        Route::post( "save_comment/{blogPostSlug}" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcCommentWriterController@addNewComment' )
             ->name( "blogetc.comments.add_new_comment" );


    } );

    Route::group( [ 'prefix' => config( "blogetc.admin_prefix" , "blog_admin" ) ] , function () {

        Route::get( "/" , '\WebDevEtc\BlogEtc\Controllers\BlogEtcAdminController@index' )
             ->name( "blogetc.admin.index" );

        Route::get( "/add_post" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcAdminController@create_post' )->name( "blogetc.admin.create_post" );
        Route::post( "/add_post" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcAdminController@store_post' )->name( "blogetc.admin.store_post" );

        Route::get( "/edit_post/{blogPostId}" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcAdminController@edit_post' )->name( "blogetc.admin.edit_post" );
        Route::patch( "/edit_post/{blogPostId}" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcAdminController@update_post' )->name( "blogetc.admin.update_post" );

        Route::delete( "/delete_post/{blogPostId}" ,
            '\WebDevEtc\BlogEtc\Controllers\BlogEtcAdminController@destroy_post' )
             ->name( "blogetc.admin.destroy_post" );

        Route::group( [ 'prefix' => "comments" , ] , function () {

            Route::get( "/" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCommentsAdminController@index' )
                 ->name( "blogetc.admin.comments.index" );

            Route::patch( "/{commentId}" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCommentsAdminController@approve' )
                 ->name( "blogetc.admin.comments.approve" );
            Route::delete( "/{commentId}" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCommentsAdminController@destroy' )
                 ->name( "blogetc.admin.comments.delete" );
        } );

        Route::group( [ 'prefix' => "categories" ] , function () {

            Route::get( "/" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCategoryAdminController@index' )
                 ->name( "blogetc.admin.categories.index" );

            Route::get( "/add_category" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCategoryAdminController@create_category' )
                 ->name( "blogetc.admin.categories.create_category" );
            Route::post( "/add_category" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCategoryAdminController@store_category' )
                 ->name( "blogetc.admin.categories.store_category" );

            Route::get( "/edit_category/{categoryId}" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCategoryAdminController@edit_category' )
                 ->name( "blogetc.admin.categories.edit_category" );

            Route::patch( "/edit_category/{categoryId}" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCategoryAdminController@update_category' )
                 ->name( "blogetc.admin.categories.update_category" );

            Route::delete( "/delete_category/{categoryId}" ,
                '\WebDevEtc\BlogEtc\Controllers\BlogEtcCategoryAdminController@destroy_category' )
                 ->name( "blogetc.admin.categories.destroy_category" );

        } );

    } );
} );

