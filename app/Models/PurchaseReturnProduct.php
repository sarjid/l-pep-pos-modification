<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // public function model()
    // {
    //     return $this->belongsTo(ProductModel::class, "product_model_id");
    // }
}
