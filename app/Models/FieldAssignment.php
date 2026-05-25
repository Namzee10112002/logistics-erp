<?php

namespace App\Models;

use Database\Factories\FieldAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldAssignment extends Model
{
    /** @use HasFactory<FieldAssignmentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assignment_code',
        'shipping_job_id',
        'field_staff_id',
        'location_id',
        'created_by',
        'assigned_date',
        'tasks',
        'status',
        'note',
        'assigned_at',
        'completed_at',
        'cancelled_at',
    ];

    public function shippingJob(): BelongsTo
    {
        return $this->belongsTo(ShippingJob::class);
    }

    public function fieldStaff(): BelongsTo
    {
        return $this->belongsTo(FieldStaff::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'tasks' => 'array',
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
