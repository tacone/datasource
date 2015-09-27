<?php

namespace Tacone\DataSource\Test;

use Config;

function findTestLaravelInstallation()
{
    if (file_exists(__DIR__ . '/../../laravel')) {
        $laravelFolder = __DIR__.'/../../laravel';
    } else {
        $laravelFolder = __DIR__ . '/../../../../..';
    }
    return $laravelFolder;
}

function bootstrapLaravel() {
    $laravel = findTestLaravelInstallation();
    if (file_exists("$laravel/bootstrap/start.php")) {
        // Laravel 4
        $app = require "$laravel/bootstrap/start.php";
        $app->boot();
        return $app;
    } else {
        // Laravel 5
        require_once "$laravel/bootstrap/autoload.php";
        $app = require_once "$laravel/bootstrap/app.php";
        if (is_bool($app)) {
         return $app;
        }
        $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        return $app;
    }
}