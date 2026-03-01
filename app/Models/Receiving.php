<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_number',
        'purchase_order_id',
        'received_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    /**
     * Get the purchase order that owns the receiving.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the receiving items for the receiving.
     */
    public function receivingItems(): HasMany
    {
        return $this->hasMany(ReceivingItem::class);
    }
}
