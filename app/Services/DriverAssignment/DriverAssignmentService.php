<?php
namespace App\Services\DriverAssignment;


use App\Services\Carpool\CarpoolService;

class DriverAssignmentService
{
    /**
     * @param $planning_days
     * @return mixed
     */
    public function assignDrivers($planning_days)
    {
        $planning_period = new PlanningPeriodManager();
        return $planning_period->setDays($planning_days)->assignDriversToTrips();
    }


    /**
     * @return array
     */
    public function getAssignmentRules()
    {
        //We will use the basic carpool rules to ensure proper structure
        $carpool_service = new CarpoolService();
        return $carpool_service->getValidationRules();
    }
}