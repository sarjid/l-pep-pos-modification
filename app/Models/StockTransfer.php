<?php

namespace App\Models;

use App\Traits\InvoiceNo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class StockTransfer extends Model
{
    use HasFactory, InvoiceNo;

    protected $guarded = [];

    public function agent()
    {
        return $this->belongsTo(User::class);
    }

     public function details()
    {
        return $this->hasMany(StockTransferDetail::class, 'stock_transfer_id');
    }


     /**
     * Get all of the products for the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,         // Final model
            StockTransferDetail::class, // Intermediate model
            'stock_transfer_id',          // Foreign key on PurchaseProduct table
            'id',                   // Local key on Product table
            'id',                   // Local key on Purchase table
            'product_id'            // Foreign key on PurchaseProduct pointing to Product
        );
    }
}
