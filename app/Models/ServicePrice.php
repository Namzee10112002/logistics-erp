<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePrice extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\ServicePriceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'package_code',
        'service_name',
        'unit',
        'unit_price',
        'is_tax_included',
    ];

    protected function casts(): array
    {
        return [
            'is_tax_included' => 'boolean',
        ];
    }
}
