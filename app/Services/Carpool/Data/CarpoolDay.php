<?php

namespace App\Services\Carpool\Data;

use Illuminate\Database\Eloquent\Model;

class CarpoolDay extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'day_label',
    ];

    /**
     * Get the carpool to which it belongs
     */
    public function carpool()
    {
        return $this->belongsTo(Carpool::class);
    }

    /**
     * Get the trips for the day
     */
    public function trips()
    {
        return $this->hasMany(CarpoolTrip::class);
    }

}