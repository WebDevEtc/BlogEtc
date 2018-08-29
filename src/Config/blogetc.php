<?php

//config for webdevetc/blogetc

return [

    'include_default_routes' => true, // set to false to not include routes.php for BlogEtcReaderController related routes
//    'include_default_controller' => true, // set to false to not auto include the BlogEtcReaderController controller
//    'include_default_admin_controller' => true, // set to false to not auto include the BlogEtcAdminController

    'blog_prefix' => "blog", // used in routes.php. If you want to your http://yoursite.com/latest-news (or anything else), then enter that here
    'admin_prefix' => "blog_admin", // similar to above, but used for the admin panel for the blog

    'use_custom_view_files' => true, // set to false to disable the use of being able to make blog posts include a view from resources/views/custom_blog_posts/*.blade.php

    'per_page' => 10, // how many posts to show per page on the blog index page


    'image_upload_enabled' => true, // true or false, if image uploading is allowed.
    'blog_upload_dir' => "blog_images", // this should be in public_path() (i.e. /public/blog_images), and should be writable

    'image_quality' => 80,


    'image_sizes' => [

        // if you set 'enabled' to false, it will clear any data for that field the next time any row is updated. However it will NOT delete the .jpg file on your file server.
        // I recommend that you only change the enabled field before any images have been uploaded!

        // Also, if you change the w/h (which are obviously in pixels :) ), it won't change any previously uploaded images.

        // There must be only three sizes - image_large, image_medium, image_thumbnail.

        'image_large' => [
            'w' => 1000,
            'h' => 700,
            'enabled' => true, // see note above
        ],
        'image_medium' => [
            'w' => 600,
            'h' => 200,
            'enabled' => true, // see note above
        ],
        'image_thumbnail' => [
            'w' => 150,
            'h' => 150,
            'enabled' => true, // see note above
        ],
    ],


    'captcha' => [

        'captcha_enabled' => true, // true = we should use a captcha, false = turn it off. If comments are disabled this makes no difference.
        'captcha_type' =>  \WebDevEtc\BlogEtc\Captcha\Basic::class, // this should be a class that implements the \WebDevEtc\BlogEtc\Interfaces\CaptchaInterface interface

        'basic_question' => "What is the opposite of white?", // a simple captcha question to always ask (if captcha_type is set to 'basic'
        'basic_answers' => "white,dark", // comma separated list of possible answers. Don't worry about case.


    ],

    ////////// RSS FEED

    'rssfeed' => [

        'cache_in_minutes' => 60, // how long (in minutes) to cache the RSS blog feed for.
        'description' => "Our blog post RSS feed", //description for the RSS feed
        'language'=>"en", // see https://www.w3.org/TR/REC-html40/struct/dirlang.html#langcodes
    ],

    ////////// comments:

    'comments' => [

        'type_of_comments_to_show' => 'built_in', // options: 'built_in' (default, uses own database for comments), 'disqus' (uses https://disqus.com/, please enter further config options below), 'disabled' (turn comments off)

        'max_num_of_comments_to_show' => 10000, // max num of comments to show on a single blog post. Set to a lower number for smaller page sizes.
        'save_ip_address' => true, // should we save the IP address in the database?
        'auto_approve_comments' => false, // should comments appear straight away on the site? or wait for approval


        'save_user_id_if_logged_in' => true, // if user is logged in, should we save that user id? (if false it will always ask for an author name, which the commenter can provide

        'user_field_for_author_name' => "name", // what field on your User model should we use when echoing out the author name? By default this should be 'name', but maybe you have it set up to use 'username' etc.


        'disqus' => [

            /**
             * The following config option can be found by looking for the following line on the embed code of your disqus code:
             *             s.src = 'https://yourusername_or_sitename.disqus.com/embed.js';
             *
             * You must enter the whole url (but not the "s.src = '" part!)
             */
            'src_url' => "https://GET_THIS_FROM_YOUR_EMBED_CODE.disqus.com/embed.js",

        ],
    ],





];