<?php

namespace DoubleThreeDigital\SimpleCommerce\Rules;

use DoubleThreeDigital\SimpleCommerce\Facades\TaxCategory;
use Illuminate\Contracts\Validation\Rule;

class TaxCategoryExists implements Rule
{
    public function passes($attribute, $value)
    {
        return TaxCategory::find($value) !== null;
    }

    public function message()
    {
        return __('Sorry, the tax category provided could not be found.');
    }
}
