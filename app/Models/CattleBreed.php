<?php

namespace App\Models;

use App\Traits\UserLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CattleBreed extends Model
{
    use HasFactory, UserLog;

    protected $guarded = [];
}
