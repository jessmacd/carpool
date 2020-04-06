<?php

namespace App\Services\DriverAssignment\AssignmentRules;


class LimitTripsPerDayRule implements DriverAssignmentRuleInterface
{
    /**
     * Always limit not more than one trip on the same day for now.  This could become configurable
     * @var int
     */
    private static $max_trips_per_day = 1;

    /**
     * @param $trip
     * @param $driver_id
     * @param $context
     * @return bool
     */
    public function driverPassesRuleForTrip($trip, $driver_id, $context) {
        //Does the driver have a trip on this day already
        $same_day_trip_count = collect(data_get($context, 'all_trips'))
            ->where('driver_id', $driver_id)
            ->where('day_key', data_get($trip, 'day_key'))
            ->count();

        //If adding this trip would cause me to exceed the limit, the assignment is not valid
        if ($same_day_trip_count + 1 > $this->getMaxTripsPerDay()) {
            return false;
        }

        return true;
    }


    /**
     * How many trips can a single driver be assigned in a day?
     * @return int
     */
    private function getMaxTripsPerDay() {
        return self::$max_trips_per_day;
    }
}