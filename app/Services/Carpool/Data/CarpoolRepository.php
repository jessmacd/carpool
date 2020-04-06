<?php

namespace App\Services\Carpool\Data;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class CarpoolRepository
{
    /**
     * @param $data
     * @return bool
     */
    public function saveCarpool($data)
    {
        //Handle some defaults
        if (!Arr::get($data, 'begins_at')) $data['begins_at'] = Carbon::now();

        //Save the high level carpool data (future: these dates would come in, carpool group id would be set, etc
        $carpool = new Carpool(Arr::only($data, ['begins_at', 'ends_at', 'planning_open']));
        $carpool->save();

        //If we saved the carpool, save its days
        if ($carpool) {
            $this->saveDays($carpool, data_get($data, 'days'));
        }

        return true;
    }


    /**
     * @param $carpool Carpool
     * @param $days_data
     */
    private function saveDays(Carpool $carpool, array $days_data)
    {
        foreach ($days_data as $day_data) {
            $day = $carpool->days()->save(new CarpoolDay($day_data));

            //Save the trips for the day
            if ($day) {
                $this->saveTrips($day, data_get($day_data, 'trips', []));
            }
        }
    }


    /**
     * @param $day
     * @param $trips_data
     */
    private function saveTrips(CarpoolDay $day, array $trips_data)
    {
        foreach ($trips_data as $trip_data) {
            $trip = $day->trips()->save(new CarpoolTrip($trip_data));

            //Save the riders  for the trip
            if ($trip) {
                $this->saveRiders($trip, data_get($trip_data, 'riders', []));
                $this->saveConflicts($trip, data_get($trip_data, 'driver_conflicts', []));
            }
        }
    }


    /**
     * @param $trip
     * @param $rider_ids
     */
    private function saveRiders(CarpoolTrip $trip, $rider_ids)
    {
        foreach ($rider_ids as $id) {
            $trip->riders()->create(['rider_id' => $id]);
        }
    }


    /**
     * @param $trip
     * @param $conflicts
     */
    private function saveConflicts(CarpoolTrip $trip, $conflicts)
    {
        foreach ($conflicts as $driver_id) {
            $trip->conflicts()->create(['driver_id' => $driver_id]);
        }
    }

}