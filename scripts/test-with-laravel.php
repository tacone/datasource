#!/usr/bin/env php
<?php

require_once __DIR__.'/../tests/utils/laravel.php';

// WARNING: this script deletes folders recursively !!

/*
 * This script will install Laravel in this package subfolder for
 * automated testing purposes.
 *
 * You don't need to use this, if you have a working Laravel installation
 * which depends on this package. In that case just `cd` in this package
 * directory and run `phpunit`
 *
 * ```bash
 * cd vendor/tacone/datasource
 * phpunit
 * ```
 *
 * Otherwise:
 *
 * ```bash
 * cd vendor/tacone/datasource
 * scripts/test-with-laravel 5.1
 * phpunit
 * ```
 *
 * In the latter case, the tests will use the laravel installation in
 * the `laravel` subfolder.
 *
 * You'll probably don't need this, we do because we need to test this
 * package with all the Laravel versions around.
 */
chdir(__DIR__.'/..');

if (!file_exists('composer.phar')) {
    echo 'downloading composer:'.PHP_EOL;
    passthru('php -r "readfile(\'https://getcomposer.org/installer\');" | php');
}
echo PHP_EOL;

$dir = __DIR__.'/../laravel';
if (file_exists($dir)) {
    echo "deleting existing laravel installation at $dir:".PHP_EOL;
    passthru("rm  $dir -rf");
}

echo PHP_EOL;

$laravelVersion = !empty($argv[1])? ' '.$argv[1]:'';

echo "Install a new Laravel$laravelVersion: ".PHP_EOL;
passthru("php composer.phar create-project laravel/laravel $dir$laravelVersion");

// change dir to laravel and suck the deps
chdir($dir);
passthru("pwd");

passthru("php ../composer.phar update");


