<?php

namespace Tacone\DataSource\Test;
require_once __DIR__.'/utils/laravel.php';

use Illuminate\Database\Schema\Blueprint;
use Schema;

class BaseTestCase extends \Illuminate\Foundation\Testing\TestCase
{
    // see: https://github.com/sebastianbergmann/phpunit/issues/856
    // and: https://github.com/sebastianbergmann/phpunit/issues/314
    protected $preserveGlobalState = false;

    public function __construct()
    {
        error_reporting(-1);
        $args = func_get_args();

        $this->includeModels();

        return call_user_func_array('parent::__construct', $args);
    }

    public function createApplication()
    {
    }

    public function refreshApplication()
    {
        $app = bootstrapLaravel();
        return $app;
    }
    public function setUp()
    {
        parent::setUp();

        \Config::set('database.default', 'sqlite');
        \Config::set('database.connections', [
            'sqlite' => array(
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ),
        ]);
        $this->createDatabase();
    }


    protected function createDatabase()
    {
        Schema::dropIfExists('customers');

        //create all tables
        Schema::table('customers', function (Blueprint $table) {
            $table->create();
            $table->increments('id');
            $table->string('name');
            $table->string('surname');
            $table->timestamps();
        });

        Schema::dropIfExists('customer_details');
        Schema::table('customer_details', function (Blueprint $table) {
            $table->create();
            $table->increments('id');
            $table->integer('customer_id')->unsigned();
            $table->string('biography');
            $table->boolean('accepts_cookies');
            $table->timestamps();
        });

        Schema::dropIfExists('orders');
        Schema::table('orders', function (Blueprint $table) {
            $table->create();
            $table->increments('id');
            $table->integer('customer_id')->unsigned();
            $table->string('code');
            $table->string('shipping');
            $table->timestamps();
        });

        Schema::dropIfExists('books');
        Schema::table('books', function (Blueprint $table) {
            $table->create();
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::dropIfExists('book_order');
        Schema::table('book_order', function (Blueprint $table) {
            $table->create();
            $table->integer('book_id')->unsigned();
            $table->integer('order_id')->unsigned();
        });


    }

    protected function includeModels()
    {
        foreach (glob(__DIR__ . '/models/*.php') as $file) {
            require_once $file;
        }
    }

    public function createModels($className, $data)
    {
        (new $className())->truncate();

        foreach ($data as $record) {
            $model = new $className();
            foreach ($record as $key => $value) {
                $model->$key = $value;
            }
            $model->save();
        }
    }
    public function createPivot($tableName, $data) {
        \DB::table($tableName)->truncate();
        foreach ($data as $record) {
            \DB::table($tableName)->insert($record);
        }
    }
}
