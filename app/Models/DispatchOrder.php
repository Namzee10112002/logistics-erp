<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispatchOrder extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\DispatchOrderFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'shipping_job_id',
        'vehicle_id',
        'driver_id',
        'dispatch_status',
        'note',
        'start_location_id',
        'end_location_id',
        'loading_percent',
        'current_latitude',
        'current_longitude',
        'start_time',
        'end_time',
        'fuel_quota',
        'toll_quota',
        'created_by',
    ];

    public function shippingJob(): BelongsTo
    {
        return $this->belongsTo(ShippingJob::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function startLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'start_location_id');
    }

    public function endLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'end_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'loading_percent' => 'integer',
        ];
    }
}
