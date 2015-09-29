<?php

namespace Tacone\DataSource\Test;

use Illuminate\Database\Eloquent\Collection;
use Tacone\DataSource\DataSource;

class HasOneTest extends BaseTestCase
{
    public function testInteraction()
    {
        // --- test behavior

        // notice we use separate objects the two, we just want to test similar behavior
        // not interaction

        $customer = new Customer();
        $source = Datasource::make(new Customer());
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';

        // eloquent always report false, datasource will report false or true
        assertFalse(isset($customer->details));
        assertFalse(isset($source['details']));
        assertNull($customer->details);
        assertNull($source['details']);
    }

    public function testBelongsToOne()
    {
        $customer = new Customer();
        $source = DataSource::make($customer);
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';
        $source['details.biography'] = 'A nice life!';
        $source['details.accepts_cookies'] = 0;
        assertSame('Frank', $source['name']);
        assertSame('Sinatra', $source['surname']);
        $source->save();
        assertSame(1, Customer::all()->count());
        assertSame(1, CustomerDetail::all()->count());

        // test that we don't create duplicates
        $source['name'] = 'Anna';
        $source['details.biography'] = 'prefers not say';

        assertSame(1, Customer::all()->count());
        assertSame(1, CustomerDetail::all()->count());
    }


    public function testExistingChildren()
    {
        // --- test load
        $this->createModels(Customer::class, []);
        $this->createModels(CustomerDetail::class, [
            ['biography' => 'A nice life!', 'accepts_cookies' => 0, 'customer_id' => 1]
        ]);

        $customer = new Customer();
        $source = Datasource::make(new Customer());
        $source['id'] = 1;
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';

        $customer->id = 1;
        $customer->name = 'Frank!';
        $customer->surname = 'Sinatra';

        $source->save();

        assertTrue(isset($source['details']));
        assertInstanceOf(CustomerDetail::class, $source['details']);

        assertModelArrayEqual(CustomerDetail::all()->toArray(), [$source['details']->toArray()]);

        // compare with how eloquent behaves:
        $this->createModels(Customer::class, []);
        $customer->save();

        assertFalse(isset($customer->details)); // always false!
        assertInstanceOf(CustomerDetail::class, $customer->details);

    }

    public function testUpdateChildren()
    {
        $this->createModels(Customer::class, [
            ['name' => 'Frankx', 'surname' => "Sinatrax"]
        ]);
        $this->createModels(CustomerDetail::class, [
            ['biography' => 'A nice life!x', 'accepts_cookies' => 1, 'customer_id' => 1]
        ]);


        $source = DataSource::make(Customer::find(1));
        $source['id'] = 1;
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';
        $source['details.biography'] = 'A nice life!';
        $source['details.accepts_cookies'] = 0;
        $source->save();

        assertModelArrayEqual([
            [
                "name" => "Frank",
                "surname" => 'Sinatra',
                "details" => [
                    "id" => "1",
                    "customer_id" => "1",
                    "biography" => "A nice life!",
                    "accepts_cookies" => "0",
                ]
            ],
        ], Customer::with('details')->get()->toArray());
        assertModelArrayEqual([
            ['name' => 'Frank', 'surname' => 'Sinatra'],
        ], Customer::all()->toArray());

        // repeat
        $source->save();

        assertModelArrayEqual([
            [
                "name" => "Frank",
                "surname" => 'Sinatra',
                "details" => [
                    "id" => "1",
                    "customer_id" => "1",
                    "biography" => "A nice life!",
                    "accepts_cookies" => "0",
                ]
            ],
        ], Customer::with('details')->get()->toArray());

        assertModelArrayEqual([
            ['biography' => 'A nice life!', 'accepts_cookies' => 0, 'customer_id' => 1]
        ], CustomerDetail::all()->toArray());

    }


    public function testCreateChildren()
    {
        $this->createModels(Customer::class, []);
        $this->createModels(Customer::class, []);

        $source = DataSource::make(new Customer());
        $source['id'] = 1;
        $source['name'] = 'Frank';
        $source['surname'] = 'Sinatra';
        $source['details.biography'] = 'A nice life!';
        $source['details.accepts_cookies'] = 0;
        $source->save();

        assertModelArrayEqual([
            [
                "name" => "Frank",
                "surname" => 'Sinatra',
                "details" => [
                    "id" => "1",
                    "customer_id" => "1",
                    "biography" => "A nice life!",
                    "accepts_cookies" => "0",
                ]
            ],
        ], Customer::with('details')->get()->toArray());
        assertModelArrayEqual([
            ['name' => 'Frank', 'surname' => 'Sinatra'],
        ], Customer::all()->toArray());

        // repeat
        $source->save();

        assertModelArrayEqual([
            [
                "name" => "Frank",
                "surname" => 'Sinatra',
                "details" => [
                    "id" => "1",
                    "customer_id" => "1",
                    "biography" => "A nice life!",
                    "accepts_cookies" => "0",
                ]
            ],
        ], Customer::with('details')->get()->toArray());

        assertModelArrayEqual([
            ['biography' => 'A nice life!', 'accepts_cookies' => 0, 'customer_id' => 1]
        ], CustomerDetail::all()->toArray());

    }
}
