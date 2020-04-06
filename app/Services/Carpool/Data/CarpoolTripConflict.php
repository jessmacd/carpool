<?php

namespace App\Services\Carpool\Data;

use Illuminate\Database\Eloquent\Model;

class CarpoolTripConflict extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'driver_id'
    ];

    /**
     * Get the carpool to which it belongs
     */
    public function trip()
    {
        return $this->belongsTo(CarpoolTrip::class);
    }
}