<?php

namespace App\Models;

use Database\Factories\RecurringExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringExpense extends Model
{
    /** @use HasFactory<RecurringExpenseFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_code',
        'name',
        'category',
        'amount',
        'cycle',
        'effective_from',
        'effective_to',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }
}
