<?php

namespace Tacone\DataSource\Test;

use Illuminate\Database\Eloquent\Collection;
use Tacone\DataSource\DataSource;

class BelongsToManyTest extends BaseTestCase
{
    public function testInteraction()
    {
        // --- test behavior

        // notice we use separate objects the two, we just want to test similar behavior
        // not interation

        $order = new Order();
        $source = Datasource::make(new Order());
        $source['code'] = 'a1';
        $source['shipping'] = 'home';

        // this is how eloquent behaves (always false)
        assertFalse(isset($order->books));
        // this is how datasource behaves (always true)
        assertTrue(isset($source['books']));
        assertInstanceOf(Collection::class, $order->books);
        assertInstanceOf(Collection::class, $source['books']);
    }

    public function testExistingChildren()
    {
        // --- test load
        $this->createModels(Order::class, []);
        $this->createModels(Book::class, [
            ['title' => 'Happiness'],
            ['title' => 'Delight'],
        ]);
        $this->createPivot('book_order', [
            ['book_id' => 1, 'order_id' => 1],
            ['book_id' => 2, 'order_id' => 1],
        ]);

        $order = new Order();
        $source = Datasource::make(new Order());
        $source['id'] = 1;
        $source['code'] = 'a1';
        $source['shipping'] = 'home';
        $source['customer_id'] = 1;
        $source->save();

        // this is how eloquent behaves (always false)
        assertFalse(isset($order->books));
        // this is how datasource behaves (always true)
        assertTrue(isset($source['books']));
        assertInstanceOf(Collection::class, $order->books);
        assertInstanceOf(Collection::class, $source['books']);
        assertModelArrayEqual(Book::all()->toArray(), $source['books']->toArray());

        // don't do this at home, folks :)
        assertSame('Happiness', $source['books.0.orders.0.books.0.title']);
        assertSame('Happiness', $source['books.0.orders.0.books.0.orders.0.books.0.orders.0.books.0.title']);
    }

    public function testUpdateChildren()
    {
        $this->createModels(Order::class, [
            ['code' => 'a1x', 'shipping' => 'homex', 'customer_id' => 1],
        ]);
        $this->createModels(Book::class, [
            ['title' => 'Happinessx'],
            ['title' => 'Delightx'],
        ]);
        $this->createPivot('book_order', [
            ['book_id' => 1, 'order_id' => 1],
            ['book_id' => 2, 'order_id' => 1],
        ]);

        $source = DataSource::make(Order::find(1));
        $source['code'] = 'a1';
        $source['shipping'] = 'home';
        $source['books.0.title'] = 'Happiness';
        $source['books.1.title'] = 'Delight';
        $source->save();

        assertModelArrayEqual([
            [
                'code' => 'a1',
                'shipping' => 'home',
                'customer_id' => 1,
            ],
        ], Order::all()->toArray());

        assertModelArrayEqual([
            ['title' => 'Happiness'],
            ['title' => 'Delight'],
        ], Book::all()->toArray());

        // repeat
        $source->save();

        assertModelArrayEqual([
            [
                'code' => 'a1',
                'shipping' => 'home',
                'customer_id' => 1,
            ],
        ], Order::all()->toArray());

        assertModelArrayEqual([
            ['title' => 'Happiness'],
            ['title' => 'Delight'],
        ], Book::all()->toArray());
    }

    public function testCreateChildren()
    {
        $this->createModels(Order::class, []);
        $this->createModels(Book::class, []);

        // test creation
        $order = new Order();
        $source = Datasource::make($order);
        $source['code'] = 'a1';
        $source['shipping'] = 'home';
        $source['customer_id'] = '1';
        $source['books.0.title'] = 'Happiness';
        $source['books.1.title'] = 'Delight';
        $source->save();

        assertModelArrayEqual([
            [
                'code' => 'a1',
                'shipping' => 'home',
                'customer_id' => 1,
            ],
        ], Order::all()->toArray());

        assertModelArrayEqual([
            [
                'title' => 'Happiness',
            ],
            [
                'title' => 'Delight',
            ],
        ], Book::all()->toArray());

        // repeat

        $source->save();

        assertModelArrayEqual([
            [
                'code' => 'a1',
                'shipping' => 'home',
                'customer_id' => 1,
            ],
        ], Order::all()->toArray());

        assertModelArrayEqual([
            [
                'title' => 'Happiness',
            ],
            [
                'title' => 'Delight',
            ],
        ], Book::all()->toArray());
    }
}
