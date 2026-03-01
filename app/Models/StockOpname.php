<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_number',
        'opname_date',
        'status',
        'approved_by',
        'submitted_at',
        'approved_at',
        'adjusted_at',
        'notes',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'adjusted_at' => 'datetime',
    ];

    /**
     * Get the user who approved the stock opname.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the stock opname items for the stock opname.
     */
    public function stockOpnameItems(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }
}
