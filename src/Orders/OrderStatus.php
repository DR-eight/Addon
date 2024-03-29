<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

enum OrderStatus: string
{
    case Cart = 'cart';
    case Placed = 'placed';
    case Dispatched = 'dispatched';
    case Cancelled = 'cancelled';

    public function is($orderStatus): bool
    {
        if (! is_string($orderStatus)) {
            $orderStatus = $orderStatus->value;
        }

        return $this->value === $orderStatus;
    }
}
