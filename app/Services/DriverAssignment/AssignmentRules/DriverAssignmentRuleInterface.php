<?php

namespace App\Services\DriverAssignment\AssignmentRules;

/**
 * Interface DriverAssignmentRuleInterface
 * @package App\Services\DriverAssignment\AssignmentRules
 */
interface DriverAssignmentRuleInterface
{
    /**
     * Truth test for whether a potential assignment is valid by the standards of the implementing rule
     * @param $trip
     * @param $driver_id
     * @param $context
     * @return mixed
     */
    public function driverPassesRuleForTrip($trip, $driver_id, $context);
}