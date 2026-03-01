<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'item_id',
        'system_quantity',
        'physical_quantity',
        'difference',
        'adjustment_type',
        'notes',
    ];

    /**
     * Get the stock opname that owns the stock opname item.
     */
    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    /**
     * Get the item that owns the stock opname item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
