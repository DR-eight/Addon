<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Checkout;

use Illuminate\Pipeline\Pipeline;

class CheckoutValidationPipeline extends Pipeline
{
    protected $pipes = [
        ValidateProductStock::class,
    ];
}
