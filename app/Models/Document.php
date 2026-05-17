<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\DocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipping_job_id',
        'doc_category',
        'document_flow',
        'tax_stage',
        'file_url',
        'uploaded_by',
        'status',
        'note',
    ];

    public function shippingJob(): BelongsTo
    {
        return $this->belongsTo(ShippingJob::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
