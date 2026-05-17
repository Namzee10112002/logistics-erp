<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\PaymentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'debit_note_id',
        'amount_paid',
        'payment_method',
        'payment_date',
        'received_by',
        'reference_no',
        'note',
    ];

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
        ];
    }
}
