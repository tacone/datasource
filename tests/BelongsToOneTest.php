<?php

namespace Tacone\DataSource\Test;

use Tacone\DataSource\DataSource;

class BelongsToOneTest extends BaseTestCase
{
    public function testInteraction()
    {
        // --- test behavior

        // notice we use separate objects the two, we just want to test similar behavior
        // not interaction

        $details = new CustomerDetail();
        $source = Datasource::make(new CustomerDetail());
        $source['biography'] = 'A nice life!';
        $source['accepts_cookies'] = 0;

        // eloquent always report false, datasource will report false or true
        assertFalse(isset($details->customer));
        assertFalse(isset($source['customer']));
        assertNull($details->customer);
        assertNull($source['customer']);
    }

    public function testBelongsToOne()
    {
        $details = new CustomerDetail();
        $source = DataSource::make($details);
        $source['biography'] = 'A nice life!';
        $source['accepts_cookies'] = 0;
        $source['customer.name'] = 'Frank';
        $source['customer.surname'] = 'Sinatra';
        assertSame('A nice life!', $source['biography']);
        assertSame(0, $source['accepts_cookies']);
        $source->save();
        assertSame(1, Customer::all()->count());
        assertSame(1, CustomerDetail::all()->count());

        // test that we don't create duplicates
        $source['biography'] = 'prefers not say';
        $source['customer.name'] = 'Frank';

        assertSame(1, Customer::all()->count());
        assertSame(1, CustomerDetail::all()->count());
    }

    public function testExistingChildren()
    {
        // --- test load
        $this->createModels(CustomerDetail::class, []);
        $this->createModels(Customer::class, [
            ['name' => 'Frank', 'surname' => 'Sinatra'],
        ]);

        $details = new CustomerDetail();
        $source = Datasource::make(new CustomerDetail());
        $source['id'] = 1;
        $source['biography'] = 'A nice life!';
        $source['accepts_cookies'] = 0;
        $source['customer_id'] = 1;

        $details->id = 1;
        $details->biography = 'A nice life!';
        $details->accepts_cookies = 0;
        $details->customer_id = 1;

        $source->save();
        assertTrue(isset($source['customer']));
        assertInstanceOf(Customer::class, $source['customer']);

        assertModelArrayEqual(Customer::all()->toArray(), [$source['customer']->toArray()]);

        // compare with how eloquent behaves:
        $this->createModels(CustomerDetail::class, []);
        $details->save();
        assertFalse(isset($details->customer)); // always false!
        assertInstanceOf(Customer::class, $details->customer);
    }

    public function testUpdateChildren()
    {
        $this->createModels(CustomerDetail::class, [
            ['biography' => 'A nice life!x', 'accepts_cookies' => '1', 'customer_id' => 1],
        ]);
        $this->createModels(Customer::class, [
            ['name' => 'Frankx', 'surname' => 'Sinatrax'],
        ]);

        $source = DataSource::make(CustomerDetail::find(1));
        $source['id'] = 1;
        $source['biography'] = 'A nice life!';
        $source['accepts_cookies'] = 0;
        $source['customer.name'] = 'Frank';
        $source['customer.surname'] = 'Sinatra';
        $source->save();

        assertModelArrayEqual([
            [
                'customer_id' => '1',
                'biography' => 'A nice life!',
                'accepts_cookies' => '0',
                'customer' => [
                    'id' => '1',
                    'name' => 'Frank',
                    'surname' => 'Sinatra',
                ],
            ],
        ], CustomerDetail::with('customer')->get()->toArray());

        assertModelArrayEqual([
            ['name' => 'Frank', 'surname' => 'Sinatra'],
        ], Customer::all()->toArray());

        // repeat
        $source->save();

        assertModelArrayEqual([
            [
                'customer_id' => '1',
                'biography' => 'A nice life!',
                'accepts_cookies' => '0',
                'customer' => [
                    'id' => '1',
                    'name' => 'Frank',
                    'surname' => 'Sinatra',
                ],
            ],
        ], CustomerDetail::with('customer')->get()->toArray());

        assertModelArrayEqual([
            ['name' => 'Frank', 'surname' => 'Sinatra'],
        ], Customer::all()->toArray());
    }

    public function testCreateChildren()
    {
        $this->createModels(CustomerDetail::class, []);
        $this->createModels(Customer::class, []);

        $source = DataSource::make(new CustomerDetail());
        $source['id'] = 1;
        $source['biography'] = 'A nice life!';
        $source['accepts_cookies'] = 0;
        $source['customer.name'] = 'Frank';
        $source['customer.surname'] = 'Sinatra';
        $source->save();

        assertModelArrayEqual([
            [
                'customer_id' => '1',
                'biography' => 'A nice life!',
                'accepts_cookies' => '0',
                'customer' => [
                    'id' => '1',
                    'name' => 'Frank',
                    'surname' => 'Sinatra',
                ],
            ],
        ], CustomerDetail::with('customer')->get()->toArray());

        assertModelArrayEqual([
            ['name' => 'Frank', 'surname' => 'Sinatra'],
        ], Customer::all()->toArray());

        // repeat
        $source->save();

        assertModelArrayEqual([
            [
                'customer_id' => '1',
                'biography' => 'A nice life!',
                'accepts_cookies' => '0',
                'customer' => [
                    'id' => '1',
                    'name' => 'Frank',
                    'surname' => 'Sinatra',
                ],
            ],
        ], CustomerDetail::with('customer')->get()->toArray());

        assertModelArrayEqual([
            ['name' => 'Frank', 'surname' => 'Sinatra'],
        ], Customer::all()->toArray());
    }
}
