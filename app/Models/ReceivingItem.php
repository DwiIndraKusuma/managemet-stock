<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_id',
        'purchase_order_item_id',
        'quantity_received',
        'quantity_accepted',
        'quantity_rejected',
        'notes',
    ];

    /**
     * Get the receiving that owns the receiving item.
     */
    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class);
    }

    /**
     * Get the purchase order item that owns the receiving item.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
