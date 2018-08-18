<?php

namespace WebDevEtc\BlogEtc;

use Illuminate\Database\Events\QueryExecuted;
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

        // load the routes file:
        if (config("blogetc.include_default_routes",true)) {
            include(__DIR__ . "/routes.php");
        }



//        \DB::listen(function(QueryExecuted $sql) {
//            dump($sql->sql);
//        });



        $this->publishes([
            __DIR__ . '/../migrations/2018_05_28_224023_create_blog_etc_posts_table.php' => database_path('migrations/2018_05_28_224023_create_blog_etc_posts_table.php')
        ]);

        $this->publishes([
            __DIR__.'/Config/blogetc.php' => config_path('blogetc.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // for public facing views:
        $this->loadViewsFrom(__DIR__ . "/Views/blogetc",'blogetc');

        // for the admin backend views:
        $this->loadViewsFrom(__DIR__ . "/Views/blogetc_admin",'blogetc_admin');
    }
}
