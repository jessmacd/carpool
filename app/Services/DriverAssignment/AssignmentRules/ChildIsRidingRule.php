<?php

namespace App\Services\DriverAssignment\AssignmentRules;


class ChildIsRidingRule implements DriverAssignmentRuleInterface
{
    /**
     * @param $trip
     * @param $driver_id
     * @param $context
     * @return bool
     */
    public function driverPassesRuleForTrip($trip, $driver_id, $context) {

        //Don't assign a  trip to a driver if their child is not riding
        $child_is_riding = false;
        $all_riders = data_get($context, 'all_riders');

        foreach (data_get($trip, 'riders', []) as $rider_id) {
            $rider_details = $all_riders->get($rider_id);
            if (data_get($rider_details, 'parent_driver_id') == $driver_id) {
                $child_is_riding = true;
            }
        }

        if (!$child_is_riding) return false;

        return true;
    }
}