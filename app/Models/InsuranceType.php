<?php

namespace App\Models;

use App\Traits\UserLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model
{
    use HasFactory, UserLog;

    protected $guarded = [];
}
