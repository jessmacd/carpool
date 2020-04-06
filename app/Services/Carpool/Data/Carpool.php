<?php

namespace App\Services\Carpool\Data;

use Illuminate\Database\Eloquent\Model;

class Carpool extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'begins_at',
        'ends_at',
        'carpool_group_id',
        'planning_open'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'begins_at',
        'ends_at'
    ];

    /**
     * Get the days for the carpool
     */
    public function days()
    {
        return $this->hasMany(CarpoolDay::class);
    }
}