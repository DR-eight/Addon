<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\GatewayRepository as ContractsGatewayRepository;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Exceptions\NoGatewayProvided;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Gateways\GatewayPurchase;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class GatewayRepository implements ContractsGatewayRepository
{
    protected $className;

    public function use($className): self
    {
        $this->className = $className;

        return $this;
    }

    public function name()
    {
        return $this->resolve()->name();
    }

    public function prepare($request, $order)
    {
        return $this->resolve()->prepare(new GatewayPrep($request, $order));
    }

    public function purchase($request, $order)
    {
        $purchase = $this->resolve()->purchase(new GatewayPurchase($request, $order));

        if ($purchase->success()) {
            Cart::find($order->id())->update([
                'gateway' => $this->className,
                'gateway_data' => $purchase->data(),
            ]);
        } else {
            // TODO: something
        }

        return $purchase;
    }

    public function purchaseRules($request, $order)
    {
        return $this->resolve()->purchaseRules();
    }

    public function getCharge($order)
    {
        return $this->resolve()->getCharge($order);
    }

    public function refundCharge($order)
    {
        $refund = $this->resolve()->refundCharge($order);

        $cart = Cart::find($order->id());
        $cart->update([
            'is_refunded' => true,
            'gateway_data' => array_merge($cart->data['gateway_data'], [
                'refund' => $refund,
            ]),
            'order_status' => 'refunded',
        ]);

        return $refund;
    }

    protected function resolve()
    {
        if (! $this->className) {
            throw new NoGatewayProvided(__('simple-commerce::gateways.no_gateway_provided'));
        }

        if (! resolve($this->className)) {
            throw new GatewayDoesNotExist(__('simple-commerce::gateways.gateway_does_not_exist'));
        }

        return resolve($this->className, [
            'config' => collect(SimpleCommerce::gateways())
                ->where('class', $this->className)->pluck('gateway_config')
                ->first(),
        ]);
    }
}
