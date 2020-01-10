<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'title', 'slug', 'uid', 'category_route', 'product_route',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
