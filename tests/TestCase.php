<?php

namespace WebDevEtc\BlogEtc\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\Database\MigrateProcessor;
use Orchestra\Testbench\TestCase as BaseTestCase;
use WebDevEtc\BlogEtc\BlogEtcServiceProvider;

/**
 * Class TestCase
 *
 * @package WebDevEtc\BlogEtc\Tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Used for Orchestra\Testbench package.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [BlogEtcServiceProvider::class];
    }

    /**
     * Set up for feature tests (migrations).
     */
    protected function featureSetUp(): void
    {
        $this->loadMigrations();
    }

    /**
     * Load migrations - to be used for feature tests.
     *
     * @return void
     */
    protected function loadMigrations(): void
    {
        $paths = __DIR__ . '/../migrations';
        $options = ['--path' => $paths];
        $options['--realpath'] = true;

        $migrator = new MigrateProcessor($this, $options);
        $migrator->up();

        $this->resetApplicationArtisanCommands($this->app);
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // ensure app.key is set.
        $app['config']->set('app.key', base64_decode('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'));

        // Ensure has correct 'sluggable' config set up:
        $app['config'] ->set('sluggable', [
            'source' => null,
            'maxLength' => null,
            'maxLengthKeepWords' => true,
            'method' => null,
            'separator' => '-',
            'unique' => true,
            'uniqueSuffix' => null,
            'includeTrashed' => false,
            'reserved' => null,
            'onUpdate' => false,
        ]);
    }
}
