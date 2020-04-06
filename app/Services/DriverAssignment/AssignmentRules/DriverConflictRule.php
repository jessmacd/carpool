<?php

namespace App\Services\DriverAssignment\AssignmentRules;


class DriverConflictRule implements DriverAssignmentRuleInterface
{
    public function driverPassesRuleForTrip($trip, $driver_id, $context) {

        // Did the driver list a conflict
        if (in_array($driver_id, data_get($trip, 'driver_conflicts', []))) {
            return false;
        }

        return true;
    }
}