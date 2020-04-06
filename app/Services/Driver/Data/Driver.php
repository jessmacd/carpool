<?php

namespace App\Services\Driver\Data;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'capacity'
    ];
}