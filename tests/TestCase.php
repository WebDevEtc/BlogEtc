<?php

namespace App\Http\Controllers {
    use Illuminate\Routing\Controller as BaseController;

    class Controller extends BaseController
    {
    }
}

// Helper classes to enable easy testing.

namespace App {
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;

    class AdminUser extends Authenticatable
    {
        use Notifiable;

        public function canManageBlogEtcPosts()
        {
            return true;
        }
    }

    class NormalUser extends Authenticatable
    {
        use Notifiable;

        public function canManageBlogEtcPosts()
        {
            return false;
        }
    }

    if (!class_exists('\App\User')) {
        class User extends NormalUser
        {
        }
    }
}

namespace WebDevEtc\BlogEtc\Tests {
    use App\AdminUser;
    use App\NormalUser;
    use App\User;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Foundation\Application;
    use Illuminate\Support\Facades\Schema;
    use Laravelium\Feed\FeedServiceProvider;
    use Orchestra\Testbench\Database\MigrateProcessor;
    use Orchestra\Testbench\TestCase as BaseTestCase;
    use Route;
    use View;
    use WebDevEtc\BlogEtc\BlogEtcServiceProvider;

    /**
     * Class TestCase.
     */
    abstract class TestCase extends BaseTestCase
    {
        /** @var User|AdminUser */
        protected $lastUser;

        /**
         * As this package does not include layouts.app, it is easier to just mock the whole View part, and concentrate
         * only on the package code in the controller. Would be interested if anyone has a suggestion on better way
         * to test this.
         *
         * @deprecated - not in use. todo: remove
         */
        protected function mockView(string $expectedView, array $viewArgumentTypes): void
        {
            $mockedReturnedView = $this->mock(\Illuminate\View\View::class);
            $mockedReturnedView->shouldReceive('render');

            $mockedView = View::shouldReceive('share')
                ->once()
                ->shouldReceive('make')
                ->once();

            $mockedView = call_user_func_array([$mockedView, 'with'], array_merge([$expectedView], $viewArgumentTypes));

            $mockedView->andReturn($mockedReturnedView)
                ->shouldReceive('exists')
                ->shouldReceive('replaceNamespace');
        }

        /**
         * Used for Orchestra\Testbench package.
         *
         * @param Application $app
         *
         * @return array
         */
        protected function getPackageProviders($app)
        {
            return [
                BlogEtcServiceProvider::class,
                FeedServiceProvider::class,
            ];
        }

        /**
         * Set up for feature tests (migrations).
         */
        protected function featureSetUp(): void
        {
            $this->loadMigrations();
            $this->withFactories(__DIR__.'/../src/Factories');

            if (!Route::has('login')) {
                Route::get('login', function () {
                })->name('login');
            }
        }

        /**
         * Load migrations - to be used for feature tests.
         */
        protected function loadMigrations(): void
        {
            $paths = [
                __DIR__.'/../migrations',
            ];

            foreach ($paths as $path) {
                $options = ['--path' => $path];
                $options['--realpath'] = true;

                $migrator = new MigrateProcessor($this, $options);
                $migrator->up();
            }

            if (!Schema::hasTable('users')) {
                Schema::create('users', static function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('name');
                    $table->string('email')->unique();
                    $table->timestamp('email_verified_at')->nullable();
                    $table->string('password');
                    $table->rememberToken();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('laravel_fulltext')) {
                Schema::create('laravel_fulltext', static function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('indexable_id');
                    $table->string('indexable_type');
                    $table->text('indexed_title');
                    $table->text('indexed_content');

                    $table->unique(['indexable_type', 'indexable_id']);

                    $table->timestamps();
                });
            }

            $this->resetApplicationArtisanCommands($this->app);
        }

        /**
         * Define environment setup.
         *
         * @param Application $app
         *
         * @return void
         */
        protected function getEnvironmentSetUp($app)
        {
            $app['config']->set('database.default', 'testbench');
            $app['config']->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);
            $app['view']->addLocation(__DIR__.'/views');
            $app['config']->set('app.key', base64_decode('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'));
            $app['config']->set('blogetc', include(__DIR__.'/../src/Config/blogetc.php'));
            $app['config']->set('sluggable', [
                'source'             => null,
                'maxLength'          => null,
                'maxLengthKeepWords' => true,
                'method'             => null,
                'separator'          => '-',
                'unique'             => true,
                'uniqueSuffix'       => null,
                'includeTrashed'     => false,
                'reserved'           => null,
                'onUpdate'           => false,
            ]);
        }

        /**
         * Be an admin user.
         */
        protected function beAdminUser(): self
        {
            $this->lastUser = new AdminUser();
            $this->lastUser->id = 1;

            $this->be($this->lastUser);

            return $this;
        }

        /**
         * Be non admin user.
         */
        protected function beNonAdminUser(): void
        {
            $this->lastUser = new NormalUser();
            $this->lastUser->id = 1;

            $this->be($this->lastUser);
        }
    }
}
