<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentSaleProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getQtyAttribute($value)
    {
        return round($value, 2);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sale()
    {
        return $this->belongsTo(AgentSale::class, 'agent_sale_id');
    }

    public function stockTransferDetail()
    {
        return $this->belongsTo(StockTransferDetail::class);
    }
}
