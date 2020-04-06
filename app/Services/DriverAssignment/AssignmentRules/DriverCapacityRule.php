<?php

namespace App\Services\DriverAssignment\AssignmentRules;


class DriverCapacityRule implements DriverAssignmentRuleInterface
{
    /**
     * @param $trip
     * @param $driver_id
     * @param $context
     * @return bool
     */
    public function driverPassesRuleForTrip($trip, $driver_id, $context) {

        //Don't assign a  trip to a driver if the number of riders exceeds capacity
        $all_drivers = data_get($context, "all_drivers");

        $driver_capacity = data_get($all_drivers->get($driver_id), "capacity");

        if ($driver_capacity && $driver_capacity < count(data_get($trip, 'riders'))) {
           return false;
        }

        return true;
    }
}