<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class EloquentOrderRepository implements RepositoryContract
{
    protected $model;

    public function __construct()
    {
        $this->model = SimpleCommerce::orderDriver()['model'];
    }

    public function all()
    {
        return (new $this->model)->all();
    }

    public function find($id): ?Order
    {
        $model = (new $this->model)->find($id);

        if (! $model) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return app(Order::class)
            ->resource($model)
            ->id($model->id)
            ->orderNumber($model->id)
            ->isPaid($model->is_paid)
            ->isShipped($model->is_shipped)
            ->isRefunded($model->is_refunded)
            ->lineItems($model->items)
            ->grandTotal($model->grand_total)
            ->itemsTotal($model->items_total)
            ->taxTotal($model->tax_total)
            ->shippingTotal($model->shipping_total)
            ->couponTotal($model->coupon_total)
            ->customer($model->customer_id)
            ->coupon($model->coupon)
            ->gateway($model->gateway)
            ->data(collect($model->data)->merge([
                'shipping_name' => $model->shipping_name,
                'shipping_address' => $model->shipping_address,
                'shipping_address_line2' => $model->shipping_address_line2,
                'shipping_city' => $model->shipping_city,
                'shipping_postal_code' => $model->shipping_postal_code,
                'shipping_region' => $model->shipping_region,
                'shipping_country' => $model->shipping_country,
                'billing_name' => $model->billing_name,
                'billing_address' => $model->billing_address,
                'billing_address_line2' => $model->billing_address_line2,
                'billing_city' => $model->billing_city,
                'billing_postal_code' => $model->billing_postal_code,
                'billing_region' => $model->billing_region,
                'billing_country' => $model->billing_country,
                'use_shipping_address_for_billing' => $model->use_shipping_address_for_billing,
                'paid_date' => $model->paid_date,
            ]));
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function save($order): void
    {
        $model = $order->resource();

        if (! $model) {
            $model = new $this->model();
        }

        $model->is_paid = $order->isPaid();
        $model->is_shipped = $order->isShipped();
        $model->is_refunded = $order->isRefunded();
        $model->items = $order->lineItems()->map->toArray();
        $model->grand_total = $order->grandTotal();
        $model->items_total = $order->itemsTotal();
        $model->tax_total = $order->taxTotal();
        $model->shipping_total = $order->shippingTotal();
        $model->coupon_total = $order->couponTotal();
        $model->customer_id = optional($order->customer())->id();
        $model->coupon = optional($order->coupon())->id();
        $model->gateway = $order->gateway();

        $model->shipping_name = $order->get('shipping_name');
        $model->shipping_address = $order->get('shipping_address');
        $model->shipping_address_line2 = $order->get('shipping_address_line2');
        $model->shipping_city = $order->get('shipping_city');
        $model->shipping_postal_code = $order->get('shipping_postal_code');
        $model->shipping_region = $order->get('shipping_region');
        $model->shipping_country = $order->get('shipping_country');

        $model->billing_name = $order->get('billing_name');
        $model->billing_address = $order->get('billing_address');
        $model->billing_address_line2 = $order->get('billing_address_line2');
        $model->billing_city = $order->get('billing_city');
        $model->billing_postal_code = $order->get('billing_postal_code');
        $model->billing_region = $order->get('billing_region');
        $model->billing_country = $order->get('billing_country');

        $model->use_shipping_address_for_billing = $order->get('use_shipping_address_for_billing') == 'true';

        // We need to do this, otherwise we'll end up duplicating data unnecessarily sometimes.
        $model->data = $order->data()->except([
            'is_paid', 'is_shipped', 'is_refunded', 'items', 'grand_total', 'items_total', 'tax_total',
            'shipping_total', 'coupon_total', 'shipping_name', 'shipping_address', 'shipping_address_line2',
            'shipping_city', 'shipping_postal_code', 'shipping_region', 'shipping_country', 'billing_name',
            'billing_address', 'billing_address_line2', 'billing_city', 'billing_postal_code', 'billing_region',
            'billing_country', 'use_shipping_address_for_billing', 'customer_id', 'coupon', 'gateway', 'paid_date',
        ]);

        $model->paid_date = $order->get('paid_date');

        $model->save();

        $order->id = $model->id;
        $order->orderNumber = $model->id;
        $order->isPaid = $model->is_paid;
        $order->isShipped = $model->is_shipped;
        $order->isRefunded = $model->is_refunded;
        // $order->lineItems = collect($model->items);
        $order->grandTotal = $model->grand_total;
        $order->itemsTotal = $model->items_total;
        $order->taxTotal = $model->tax_total;
        $order->shippingTotal = $model->shipping_total;
        $order->couponTotal = $model->coupon_total;
        $order->customer = $model->customer_id ? Customer::find($model->customer_id) : null;
        $order->coupon = $model->coupon;
        $order->gateway = $model->gateway;

        $order->data = collect($model->data)->merge([
            'shipping_name' => $model->shipping_name,
            'shipping_address' => $model->shipping_address,
            'shipping_address_line2' => $model->shipping_address_line2,
            'shipping_city' => $model->shipping_city,
            'shipping_postal_code' => $model->shipping_postal_code,
            'shipping_region' => $model->shipping_region,
            'shipping_country' => $model->shipping_country,
            'billing_name' => $model->billing_name,
            'billing_address' => $model->billing_address,
            'billing_address_line2' => $model->billing_address_line2,
            'billing_city' => $model->billing_city,
            'billing_postal_code' => $model->billing_postal_code,
            'billing_region' => $model->billing_region,
            'billing_country' => $model->billing_country,
            'use_shipping_address_for_billing' => $model->use_shipping_address_for_billing,
            'paid_date' => $model->paid_date,
        ]);

        $order->resource = $model;
    }

    public function delete($order): void
    {
        $order->resource()->delete();
    }

    public static function bindings(): array
    {
        return [];
    }
}