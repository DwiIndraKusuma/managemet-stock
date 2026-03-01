<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'vendor_name',
        'status',
        'total_amount',
        'approved_by',
        'approved_at',
        'sent_at',
        'confirmed_at',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get the user who approved the purchase order.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the purchase order items for the purchase order.
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the receivings for the purchase order.
     */
    public function receivings(): HasMany
    {
        return $this->hasMany(Receiving::class);
    }
}
