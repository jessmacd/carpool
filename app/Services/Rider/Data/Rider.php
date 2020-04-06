<?php

namespace App\Services\Rider\Data;

use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    protected $fillable = [
        'name',
        'parent_driver_id'
    ];
}