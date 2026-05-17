<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\DriverFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'driver_code',
        'full_name',
        'phone',
        'license_number',
        'status',
        'start_date',
        'rank',
        'contract_expiry',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dispatchOrders(): HasMany
    {
        return $this->hasMany(DispatchOrder::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'contract_expiry' => 'date',
        ];
    }
}
