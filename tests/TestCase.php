<?php

namespace WebDevEtc\BlogEtc\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use WebDevEtc\BlogEtc\BlogEtcServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [BlogEtcServiceProvider::class];
    }
}
