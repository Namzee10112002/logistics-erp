<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\LocationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'location_name',
        'type',
        'address',
        'province',
    ];

    public function pickupJobs(): HasMany
    {
        return $this->hasMany(ShippingJob::class, 'pickup_location_id');
    }

    public function deliveryJobs(): HasMany
    {
        return $this->hasMany(ShippingJob::class, 'delivery_location_id');
    }
}
