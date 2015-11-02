# DataSource

[![Build Status](https://travis-ci.org/tacone/datasource.svg?branch=master)](https://travis-ci.org/tacone/datasource)
[![Coverage Status](https://coveralls.io/repos/tacone/datasource/badge.svg?branch=master&service=github)](https://coveralls.io/github/tacone/datasource?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tacone/datasource/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tacone/datasource/?branch=master)

A generic, wrapper object to easily access arrays, plain objects, laravel models
using the dotted syntax.

For eloquent models, accessing and saving of related models is supported,
even though not all the relationship kinds are there yet.

```php
$source = new DataSource(new Customer());
$source['name'] = 'Frank';
$source['surname'] = 'Sinatra';
$source['details.email'] = 'frank@example.com';
$source['details.twitter'] = '@therealfrankie';

$source->save();
```

## Requirements

- PHP 5.5.0
- Laravel 4.2 or greater.

Laravel is not required if you only need to manipulate POJOs and arrays.

## Testing

To test this package you need to install it under a working Laravel
installation. Then `cd` in the package folder and run `php unit`.

If you just want to develop this package and want to set up an ad hoc
installation of Laravel, you can use `script/test-with-laravel.php`.
