<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\HasValidFormParameters;
use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
{
    use HasValidFormParameters;

    public function authorize()
    {
        return $this->hasValidFormParameters();
    }

    public function rules()
    {
        return [];
    }
}
