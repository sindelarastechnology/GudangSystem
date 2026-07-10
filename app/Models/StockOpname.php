<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    protected $fillable = [
        'opname_number',
        'opname_date',
        'warehouse_id',
        'status',
        'started_at',
        'finalized_at',
        'cancelled_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'started_at' => 'datetime',
        'finalized_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class);
    }

    public function isCounting(): bool
    {
        return $this->status === 'counting';
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
