<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity_available',
        'quantity_reserved',
        'quantity_in_transit',
        'last_movement_at',
    ];

    protected $casts = [
        'last_movement_at' => 'datetime',
    ];

    /**
     * Get the item that owns the inventory.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
