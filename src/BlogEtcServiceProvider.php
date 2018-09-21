<?php

namespace WebDevEtc\BlogEtc;

use Illuminate\Support\ServiceProvider;

class BlogEtcServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (config("blogetc.include_default_routes",true)) {
            include(__DIR__ . "/routes.php");
        }

        $this->publishes([
            __DIR__ . '/../migrations/2018_05_28_224023_create_blog_etc_posts_table.php'
                => database_path('migrations/2018_05_28_224023_create_blog_etc_posts_table.php')
        ]);
        $this->publishes([
            __DIR__ . '/../migrations/2018_09_16_224023_add_author_and_url_blog_etc_posts_table.php'
            => database_path('migrations/2018_09_16_224023_add_author_and_url_blog_etc_posts_table.php')
        ]);

        $this->publishes([
            __DIR__.'/Config/blogetc.php' => config_path('blogetc.php'),
        ]);


        $this->publishes([
            __DIR__ . '/css/blogetc_admin_css.css' => public_path('blogetc_admin_css.css'),
        ]);


    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // for public facing views (view("blogetc::BLADEFILE")):
        $this->loadViewsFrom(__DIR__ . "/Views/blogetc",'blogetc');

        // for the admin backend views ( view("blogetc_admin::BLADEFILE") )
        $this->loadViewsFrom(__DIR__ . "/Views/blogetc_admin",'blogetc_admin');
    }
}
