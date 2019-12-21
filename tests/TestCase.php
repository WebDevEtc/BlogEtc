<?php

namespace WebDevEtc\BlogEtc\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\Database\MigrateProcessor;
use Orchestra\Testbench\TestCase as BaseTestCase;
use View;
use WebDevEtc\BlogEtc\BlogEtcServiceProvider;

/**
 * Class TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * As this package does not include layouts.app, it is easier to just mock the whole View part, and concentrate
     * only on the package code in the controller. Would be interested if anyone has a suggestion on better way
     * to test this.
     * @param string $expectedView
     * @param array $viewArgumentTypes
     */
    protected function mockView(string $expectedView, array $viewArgumentTypes): void
    {
        // Mocked view to return:
        $mockedReturnedView = $this->mock(\Illuminate\View\View::class);
        $mockedReturnedView->shouldReceive('render');

        // Mock the main view() calls in controller.
        $mockedView = View::shouldReceive('share')
            ->once()
            ->shouldReceive('make')
            ->once();

        $mockedView = call_user_func_array([$mockedView, 'with'], array_merge([$expectedView] , $viewArgumentTypes));

        $mockedView->andReturn($mockedReturnedView)
            ->shouldReceive('exists')
            ->shouldReceive('replaceNamespace');
    }

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
        $this->withFactories(__DIR__ . '/../src/Factories');
    }

    /**
     * Load migrations - to be used for feature tests.
     *
     * @return void
     */
    protected function loadMigrations(): void
    {
        $paths = __DIR__.'/../migrations';
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
        $app['config']->set('sluggable', [
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
