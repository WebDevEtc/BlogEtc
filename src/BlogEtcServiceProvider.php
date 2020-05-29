<?php

namespace WebDevEtc\BlogEtc;

use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use LogicException;
use Swis\Laravel\Fulltext\ModelObserver;
use WebDevEtc\BlogEtc\Gates\GateTypes;
use WebDevEtc\BlogEtc\Models\BlogEtcPost;
use WebDevEtc\BlogEtc\Models\Post;

class BlogEtcServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (false == config('blogetc.search.search_enabled')) {
            ModelObserver::disableSyncingFor(Post::class);
            // Do not remove legacy BlogEtcPost here:
            ModelObserver::disableSyncingFor(BlogEtcPost::class);
        }

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
        if (!Gate::has(GateTypes::MANAGE_ADMIN)) {
            Gate::define(GateTypes::MANAGE_ADMIN, static function (?Model $user) {
                // Do not copy the internals for this gate, as it provides backwards compatibility.
                if (!$user) {
                    return false;
                }

                if ($user && method_exists($user, 'canManageBlogEtcPosts')) {
                    // Fallback for legacy users.
                    // Deprecated.
                    // Do not add canManageBlogEtcPosts to your user model. Instead ∂efinte a gate.
                    return $user->canManageBlogEtcPosts();
                }

                throw new LogicException('You must implement your own gate in AuthServiceProvider for the \WebDevEtc\BlogEtc\Gates\GateTypes::MANAGE_ADMIN gate.');
                // Add something like the following to AuthServiceProvider:

//                Gate::define(GateTypes::MANAGE_ADMIN, static function (?Model $user) {
//                    Implement your logic to allow or disallow admin access for $user
//                    return $model->is_admin === true;
//                    or:
//                    return $model->email === 'your-email@your-site.com';
//                });
            });
        }

        /*
         * For people to add comments to your blog posts. By default it will allow anyone - you can add your
         * own logic here if needed.
         */
        if (!Gate::has(GateTypes::ADD_COMMENTS)) {
            Gate::define(GateTypes::ADD_COMMENTS, static function (?Model $user) {
                return true;
            });
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
