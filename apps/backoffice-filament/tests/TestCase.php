<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        // Doppler injects DB_DATABASE=dev before PHPUnit; force test DB here.
        putenv('DB_DATABASE=hkgold_membership_test');
        $_ENV['DB_DATABASE'] = 'hkgold_membership_test';
        $_SERVER['DB_DATABASE'] = 'hkgold_membership_test';

        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
