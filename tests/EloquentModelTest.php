<?php

namespace Tacone\DataSource\Test;

use Tacone\DataSource\DataSource;
use Tacone\DataSource\EloquentModelDataSource;

class EloquentModelTest extends BaseTestCase
{
    public function testMake()
    {
        assertInstanceOf(EloquentModelDataSource::class, DataSource::make(new Customer()));
    }

    public function testToArray()
    {
        $customer = new Customer();
        $customer->name = 'Frank';
        $customer->surname = 'Sinatra';
        $source = DataSource::make($customer);
        assertSame([
            'name' => 'Frank',
            'surname' => 'Sinatra',
        ], $source->toArray());
    }

    public function testGetSet()
    {
        $customer = new Customer();
        $customer->name = 'Frank';
        $customer->surname = 'Sinatra';
        $source = DataSource::make($customer);
        assertSame('Frank', $customer->name);
        assertSame('Frank Sinatra', $customer->full_name);
        assertSame('Frank', $source['name']);
        assertSame('Frank Sinatra', $source['full_name']);

        $customer = new Customer();
        $source = DataSource::make($customer);
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';
        assertSame('Frank', $customer->name);
        assertSame('Frank Sinatra', $customer->full_name);
        assertSame('Frank', $source['name']);
        assertSame('Frank Sinatra', $source['full_name']);

        $source->save();
        assertSame(1, Customer::all()->count());

        // now let's check if the update works
        $source['name'] = 'Jake';
        assertSame('Jake', $source['name']);
        $source->save();
        assertSame(1, Customer::all()->count());
        assertSame('Jake', Customer::find($source['id'])->name);

        // again, and to test cache collisions
        $customer = new Customer();
        $source = DataSource::make($customer);
        $source['name'] = 'Brandon';
        $source['surname'] = 'Lee';

        assertSame('Brandon', $customer->name);
        assertSame('Brandon Lee', $customer->full_name);
        assertSame('Brandon', $source['name']);
        assertSame('Brandon Lee', $source['full_name']);
        $source->save();

        assertSame(2, Customer::all()->count());
    }

    public function testUnset()
    {
        $customer = new Customer();
        $customer->name = 'Frank';
        $customer->surname = 'Sinatra';
        $source = DataSource::make($customer);
        unset($source['surname']);
        assertSame('Frank', $customer->name);
        assertSame(null, $customer->surname);
        assertSame('Frank', $source['name']);
        assertSame(null, $source['surname']);

        $customer = new Customer();
        $source = DataSource::make($customer);
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';
        unset($source['surname']);
        assertSame('Frank', $customer->name);
        assertSame(null, $customer->surname);
        assertSame('Frank', $source['name']);
        assertSame(null, $source['surname']);
    }

    public function testHasOne()
    {
        $customer = new Customer();
        $source = DataSource::make($customer);
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';
        $source['details.biography'] = 'A nice life!';
        $source['details.accepts_cookies'] = 0;
        $source['details.accepts_cookies'] = 0; // test cache

        assertSame('Frank', $source['name']);
        assertSame('A nice life!', $source['details.biography']);
        assertSame(0, $source['details.accepts_cookies']);

        $source->save();

        assertSame(1, Customer::all()->count());
        assertSame(1, CustomerDetail::all()->count());

        // test everything's saved
        $result = Customer::all()->first();
        assertSame('A nice life!', $result->details->biography);
        assertEquals(0, $result->details->accepts_cookies);

        // test we don't create duplicates

        $source['surname'] = 'Sinatrax';
        $source['details.biography'] = 'prefers not say';
        $source['details.accepts_cookies'] = 1;

        assertSame(1, Customer::all()->count());
        assertSame(1, CustomerDetail::all()->count());

        return;
    }
}
