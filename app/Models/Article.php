<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'stock',
        'purchase_price',
        'sale_price',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }
}
