<?php

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryQueryBuilder;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\RefreshContent;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use Spatie\TestTime\TestTime;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

uses(SetupCollections::class);
uses(RefreshContent::class);

beforeEach(function () {
    $this->repository = new EntryOrderRepository;
});

it('can get all orders', function () {
    Order::make()->id('one')->save();
    Order::make()->id('two')->save();
    Order::make()->id('three')->save();

    $orders = Order::all();

    expect($orders->count())->toBe(3);
    expect($orders->map->id()->toArray())->toBe(['one', 'two', 'three']);
});

it('can query orders', function () {
    TestTime::freeze('Y-m-d H:i:s', '2024-01-29 13:40:25');

    Order::make()->id('one')
        ->status(OrderStatus::Cart)
        ->paymentStatus(PaymentStatus::Unpaid)
        ->set('status_log', [
            ['status' => 'cart', 'timestamp' => Carbon::parse('2024-01-27 15:00:00')->timestamp, 'data' => []],
        ])
        ->save();

    Order::make()
        ->id('two')
        ->status(OrderStatus::Placed)
        ->paymentStatus(PaymentStatus::Paid)
        ->set('status_log', [
            ['status' => 'placed', 'timestamp' => Carbon::parse('2024-01-27 15:00:00')->timestamp, 'data' => []],
            ['status' => 'paid', 'timestamp' => Carbon::parse('2024-01-27 17:55:00')->timestamp, 'data' => []],
        ])
        ->save();

    Order::make()
        ->id('three')
        ->status(OrderStatus::Dispatched)
        ->paymentStatus(PaymentStatus::Paid)
        ->set('status_log', [
            ['status' => 'placed', 'timestamp' => Carbon::parse('2024-01-27 15:00:00')->timestamp, 'data' => []],
            ['status' => 'paid', 'timestamp' => Carbon::parse('2024-01-27 15:00:00')->timestamp, 'data' => []],
            ['status' => 'dispatched', 'timestamp' => Carbon::parse('2024-01-29 12:12:12')->timestamp, 'data' => []],
        ])
        ->save();

    // Ensure all 3 orders are returned when we're not doing any filtering.
    $query = Order::query();
    expect($query)->toBeInstanceOf(EntryQueryBuilder::class);
    expect($query->count())->toBe(3);

    // Ensure a specific order is returned when we're filtering by ID.
    $query = Order::query()->where('id', 'one');
    expect($query->count())->toBe(1);
    expect($query->get()[0])->toBeInstanceOf(OrderContract::class);

    // Ensure we can filter by order status.
    $query = Order::query()->whereOrderStatus(OrderStatus::Cart);
    expect($query->count())->toBe(1);
    expect($query->get()[0])
        ->toBeInstanceOf(OrderContract::class)
        ->and($query->get()[0]->id())->toBe('one');

    // Ensure we can filter by payment status.
    $query = Order::query()->wherePaymentStatus(PaymentStatus::Paid);
    expect($query->count())->toBe(2);
    expect($query->get()[0])
        ->toBeInstanceOf(OrderContract::class)
        ->and($query->get()[0]->id())->toBe('two');
    expect($query->get()[1])
        ->toBeInstanceOf(OrderContract::class)
        ->and($query->get()[1]->id())->toBe('three');

    // Query by status log timestamps
    $query = Order::query()->whereStatusLogDate(PaymentStatus::Paid, Carbon::parse('2024-01-27'));
    expect($query->count())->toBe(2);
    expect($query->get()[0])
        ->toBeInstanceOf(OrderContract::class)
        ->and($query->get()[0]->id())->toBe('two');
    expect($query->get()[1])
        ->toBeInstanceOf(OrderContract::class)
        ->and($query->get()[1]->id())->toBe('three');
});

it('can find order', function () {
    Order::make()->id('one')->save();

    $order = Order::find('one');

    expect($order)->toBeInstanceOf(OrderContract::class);
});

it('can make order', function () {
    $order = Order::make();

    expect($order)->toBeInstanceOf(OrderContract::class);
});

it('can save order', function () {
    $order = Order::make()->grandTotal(1000);

    expect($order->resource())->toBeNull();

    $order->save();

    expect($order->resource())->toBeInstanceOf(\Statamic\Contracts\Entries\Entry::class);
});

it('can delete order', function () {
    $order = Order::make()->id('one')->save();

    expect($order->resource())->toBeInstanceOf(\Statamic\Contracts\Entries\Entry::class);

    $order->delete();

    expect($order->resource()->fresh())->toBeNull();
});

it('can generate order number from minimum order number', function () {
    app()['config']->set('simple-commerce.minimum_order_number', 1000);

    $generateOrderNumber = (new Invader($this->repository))->generateOrderNumber();

    expect(1000)->toBe($generateOrderNumber);
});

it('can generate order number from previous order titles', function () {
    Collection::find('orders')->titleFormats([])->save();

    Entry::make()
        ->collection('orders')
        ->data(['title' => '#1234'])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['title' => '#1235'])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['title' => '#1236'])
        ->save();

    $generateOrderNumber = (new Invader($this->repository))->generateOrderNumber();

    expect(1237)->toBe($generateOrderNumber);
});

it('can generate order number from previous order number', function () {
    Collection::find('orders')->titleFormats([
        'default' => '#{order_number}',
    ])->save();

    Entry::make()
        ->collection('orders')
        ->data(['order_number' => 6001])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['order_number' => 6002])
        ->save();

    Entry::make()
        ->collection('orders')
        ->data(['order_number' => 6003])
        ->save();

    $generateOrderNumber = (new Invader($this->repository))->generateOrderNumber();

    expect(6004)->toBe($generateOrderNumber);
});
