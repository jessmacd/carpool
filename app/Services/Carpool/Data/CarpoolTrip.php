<?php

namespace App\Services\Carpool\Data;

use Illuminate\Database\Eloquent\Model;

class CarpoolTrip extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'time',
        'driver_id',
    ];

    /**
     * Get the carpool to which it belongs
     */
    public function day()
    {
        return $this->belongsTo(CarpoolDay::class);
    }

    /**
     * Get driver conflicts
     */
    public function conflicts()
    {
        return $this->hasMany(CarpoolTripConflict::class);
    }

    /**
     * Get riders
     */
    public function riders()
    {
        return $this->hasMany(CarpoolTripRider::class);
    }

}