<?php

namespace Tacone\DataSource\Test;

use Tacone\DataSource\DataSource;
use Tacone\DataSource\ObjectDataSource;

class ObjectDataSourceTest extends DataSourceTest
{
    protected function make(array $var)
    {
        // First we convert the array to a json string
        $json = json_encode($var);
        // The we convert the json string to a stdClass()
        $object = (object) json_decode($json);

        return $object;
    }

    public function testMake()
    {
        $this->assertEquals(ObjectDataSource::class, get_class(DataSource::make(new \stdClass())));
        // pass an non stdClass instance
        $this->assertEquals(ObjectDataSource::class, get_class(DataSource::make($this)));
    }

    public function testMakeError()
    {
        // pass something else
        $this->setExpectedException(\RuntimeException::class);
        $this->assertTrue(new ObjectDataSource('a string'));
    }
}
