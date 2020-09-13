<?php

namespace WebDevEtc\BlogEtc;

use Gate;
use Illuminate\Support\ServiceProvider;
use WebDevEtc\BlogEtc\Gates\GateTypes;

class BlogEtcServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('blogetc.include_default_routes', true)) {
            include __DIR__.'/routes.php';
        }

        foreach ([
            '2018_05_28_224023_create_blog_etc_posts_table.php',
            '2018_09_16_224023_add_author_and_url_blog_etc_posts_table.php',
            '2018_09_26_085711_add_short_desc_textrea_to_blog_etc.php',
            '2018_09_27_122627_create_blog_etc_uploaded_photos_table.php',
        ] as $file) {
            $this->publishes([
                __DIR__.'/../migrations/'.$file => database_path('migrations/'.$file),
            ]);
        }

        // Set up default gates to allow/disallow access to features.
        $this->setupDefaultGates();

        $this->publishes([
            __DIR__.'/Views/blogetc'             => base_path('resources/views/vendor/blogetc'),
            __DIR__.'/Config/blogetc.php'        => config_path('blogetc.php'),
            __DIR__.'/css/blogetc_admin_css.css' => public_path('blogetc_admin_css.css'),
        ]);
    }

    /**
     * Set up default gates.
     */
    protected function setupDefaultGates(): void
    {
        if (!Gate::has(GateTypes::MANAGE_BLOG_ADMIN)) {
            Gate::define(GateTypes::MANAGE_BLOG_ADMIN, include(__DIR__.'/Gates/DefaultAdminGate.php'));
        }

        /*
         * For people to add comments to your blog posts. By default it will allow anyone - you can add your
         * own logic here if needed.
         */
        if (!Gate::has(GateTypes::ADD_COMMENT)) {
            Gate::define(GateTypes::ADD_COMMENT, include(__DIR__.'/Gates/DefaultAddCommentsGate.php'));
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadViewsFrom(__DIR__.'/Views/blogetc_admin', 'blogetc_admin');

        // if you do the vendor:publish, these will be copied to /resources/views/vendor/blogetc anyway
        $this->loadViewsFrom(__DIR__.'/Views/blogetc', 'blogetc');
    }
}
