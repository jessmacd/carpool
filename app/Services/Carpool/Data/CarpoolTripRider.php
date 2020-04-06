<?php

namespace App\Services\Carpool\Data;

use Illuminate\Database\Eloquent\Model;

class CarpoolTripRider extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'rider_id'
    ];

    /**
     * Get the carpool to which it belongs
     */
    public function trip()
    {
        return $this->belongsTo(CarpoolTrip::class);
    }

}