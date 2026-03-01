<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'item_id',
        'quantity',
        'notes',
    ];

    /**
     * Get the request that owns the request item.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * Get the item that owns the request item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
