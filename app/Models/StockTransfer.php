<?php

namespace App\Models;

use App\Traits\InvoiceNo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
