<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\TaxRates;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('delete tax rates');
    }

    public function rules()
    {
        return [];
    }
}
