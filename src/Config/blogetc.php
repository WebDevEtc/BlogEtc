<?php

//config for webdevetc/blogetc

use WebDevEtc\BlogEtc\Captcha\Basic;

return [
    // set to false to not include routes.php for BlogEtcReaderController and admin related routes. Default: true. If you disable this, you will have to manually copy over the data from routes.php and add it to your web.php.
    'include_default_routes' => true,

    // used in routes.php. If you want to your http://yoursite.com/latest-news (or anything else), then enter that here. Default: blog
    'blog_prefix' => 'blog',
    // similar to above, but used for the admin panel for the blog. Default: blog_admin
    'admin_prefix' => 'blog_admin',

    // set to false to disable the use of being able to make blog posts include a view from resources/views/custom_blog_posts/*.blade.php. Default: false. Set to true to use this feature. Default: false
    'use_custom_view_files' => false,

    // how many posts to show per page on the blog index page.
    // Default: 10
    'per_page' => 10,

    // Are image uploads enabled?
    // Default: true
    'image_upload_enabled' => true,

    // This should be in public_path() (i.e. /public/blog_images), and should be writable
    // (be sure this directory is writable!)
    // Default: blog_images
    'blog_upload_dir' => 'blog_images',

    // This is used when uploading images :
    //                              @ini_set('memory_limit', config("blogetc.memory_limit"));
    //                            See PHP.net for detailso
    //                            Set to false to not set any value.
    'memory_limit' => '2048M',

    // Should it echo out raw HTML post body (with {!! ... !!})? This is not safe if you do not trust your writers!
    // Do not set to true if you don't trust your blog post writers. They could put in any HTML or JS code.
    // This will apply to all posts (past and future).
    // (you should disable this (set to false) if you don't trust your blog writers).
    // Default: true
    'echo_html' => true,

    // If echo_html is false, before running the post body in e(), it can run strip_tags
    // Default: false
    'strip_html' => false,

    // If echo_html is false, should it wrap post body in nl2br()?
    // Default: true
    'auto_nl2br' => true,

    // Use a WYSIWYG editor for posts (rich text editor)?
    // This will load scripts from https://cdn.ckeditor.com/4.10.0/standard/ckeditor.js
    // echo_html must be set to true for this to have an effect.
    // Default: true
    'use_wysiwyg' => true,

    // what image quality to use when saving images. higher = better + bigger sizes. Around 80 is normal.
    // Default: 80
    'image_quality' => 80,

    // Array of image sizes.
    'image_sizes' => [
        // if you set 'enabled' to false, it will clear any data for that field the next time any row is updated.
        // However it will NOT delete the .jpg file on your file server.
        // I recommend that you only change the enabled field before any images have been uploaded!

        'image_large' => [ // this key must start with 'image_'. This is what the DB column must be named
            // width in pixels
            'w' => 1000,
            //height
            'h' => 700,
            // same as the main key, but WITHOUT 'image_'.
            'basic_key' => 'large',
            // description, used in the admin panel
            'name' => 'Large',
            // see note above
            'enabled' => true,
            // if true then we will crop and resize to exactly w/h. If false then it will maintain proportions, with a max width of 'w' and max height of 'h'
            'crop' => true,
        ],
        'image_medium' => [ // this key must start with 'image_'. This is what the DB column must be named
            // width in pixels
            'w' => 600,
            //height
            'h' => 400,
            // same as the main key, but WITHOUT 'image_'.
            'basic_key' => 'medium',
            // description, used in the admin panel
            'name' => 'Medium',
            // see note above
            'enabled' => true,
            // if true then we will crop and resize to exactly w/h. If false then it will maintain proportions, with a max width of 'w' and max height of 'h'. If you use these images as part of your website template then you should probably have this to true.
            'crop' => true,
        ],
        'image_thumbnail' => [ // this key must start with 'image_'. This is what the DB column must be named
            'w'         => 150, // width in pixels
            'h'         => 150, //height
            'basic_key' => 'thumbnail', // same as the main key, but WITHOUT 'image_'.
            'name'      => 'Thumbnail', // description, used in the admin panel
            'enabled'   => true, // see note above
        ],

        // you can add more fields here, but make sure that you create the relevant database columns too!
        // They must be in the same format as the default ones - image_xxxxx (and this db column must exist on the blog_etc_posts table)

        /*
        'image_custom_example_size' => [ // << MAKE A DB COLUM WITH THIS NAME.
                                         //   You can name it whatever you want, but it must start with image_
            'w' => 123,                  // << DEFINE YOUR CUSTOM WIDTH/HEIGHT
            'h' => 456,
            'basic_key' =>
                  "custom_example_size", // << THIS SHOULD BE THE SAME AS THE KEY, BUT WITHOUT THE image_
            'name' => "Test",            // A HUMAN READABLE NAME
            'enabled' => true,           // see note above about enabled/disabled
            ],
        */
        // Create the custom db table by doing
        //  php artisan make:migration --table=blog_etc_posts AddCustomBlogImageSize
        //   then adding in the up() method:
        //       $table->string("image_custom_example_size")->nullable();
        //    and in the down() method:
        //        $table->dropColumn("image_custom_example_size"); for the down()
        // then run
        //   php artisan migrate
    ],

    'captcha' => [
        // true = we should use a captcha, false = turn it off. If comments are disabled this makes no difference.
        'captcha_enabled' => true,
        // this should be a class that implements the \WebDevEtc\BlogEtc\Interfaces\CaptchaInterface interface
        'captcha_type' => Basic::class,
        // a simple captcha question to always ask (if captcha_type is set to 'basic'
        'basic_question' => 'What is the opposite of white?',
        // comma separated list of possible answers. Don't worry about case.
        'basic_answers' => 'black,dark',
    ],

    ////////// RSS FEED

    'rssfeed' => [
        'should_shorten_text'       => true, // boolean. Default: true. Should we shorten the text in rss feed?
        'text_limit'                => 100, // max length of description text in the rss feed
        'posts_to_show_in_rss_feed' => 10,  // how many posts should we show in the rss feed
        'cache_in_minutes'          => 60, // how long (in minutes) to cache the RSS blog feed for.
        'description'               => 'Our blog post RSS feed', //description for the RSS feed
        'language'                  => 'en', // see https://www.w3.org/TR/REC-html40/struct/dirlang.html#langcodes
    ],

    // Comments settings:
    'comments' => [
        // What type (if any) of comments/comment form to show.
        // options:
        //      'built_in' (default, uses own database for comments),
        //      'disqus' (uses https://disqus.com/, please enter further config options below),
        //      'custom' (will load blogetc::partials.custom_comments, which you can copy to your vendor view dir to customise
        //      'disabled' (turn comments off)
        // default: built_in
        'type_of_comments_to_show' => 'built_in',

        // max num of comments to show on a single blog post. Set to a lower number for smaller page sizes. No comment pagination is built in yet.
        'max_num_of_comments_to_show' => 1000,

        // should we save the IP address in the database?
        // Default: true
        'save_ip_address' => true,

        //should comments appear straight away on the site (set this to true)? or wait for approval (set to false)
        // default: false
        'auto_approve_comments' => false,

        // if user is logged in, should we save that user id? (if false it will always ask for an author name, which the commenter can provide
        'save_user_id_if_logged_in' => true,

        // what field on your User model should we use when echoing out the author name? By default this should be 'name', but maybe you have it set up to use 'username' etc.
        'user_field_for_author_name' => 'name',

        // show 'author email' on the form ?
        'ask_for_author_email' => true,

        // require an email (make sure ask_for_author_email is true if you want to use this)
        'require_author_email' => false,

        // show 'author website' on the form, show the link when viewing the comment
        'ask_for_author_website' => true,

        'disqus' => [
            // only applies if comments.type_of_comments_to_show is set to 'disqus'
            //              The following config option can be found by looking for the following line on the embed code of your disqus code:
            //                          s.src = 'https://yourusername_or_sitename.disqus.com/embed.js';
            //
            //             You must enter the whole url (but not the "s.src = '" part!)
            // enter the url here, from the html snippet disqus provides
            'src_url' => 'https://GET_THIS_FROM_YOUR_EMBED_CODE.disqus.com/embed.js',
        ],
    ],

    // Search config:
    'search' => [
        // Is search enabled? By default this is disabled, but you can easily turn it on.
        // Default: false
        // [Search is temporarily completely disabled - will return in a future version soon. Sorry!]
        'search_enabled' => false,
    ],
];
