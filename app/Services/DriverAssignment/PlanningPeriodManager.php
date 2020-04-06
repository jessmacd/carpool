<?php

namespace App\Services\DriverAssignment;

use App\Services\Driver\DriverService;
use App\Services\Rider\RiderService;

class PlanningPeriodManager
{
    /**
     * @var array
     */
    private $planning_days = [];


    /**
     * @var array
     */
    private $trips = [];


    /**
     * @param $days
     * @return $this
     */
    public function setDays($days)
    {
        //Flatten days out into a list of assignable trips
        if (is_array($days)) {
            $this->planning_days = $days;
            $this->setTrips();
        }
        return $this;
    }


    /**
     * Assign drivers
     */
    public function assignDriversToTrips()
    {
        //Set up the needed data to make assignments
        $trip_manager = new TripManager;
        $trip_manager
            ->setTrips($this->trips)
            ->setAllDrivers($this->getDrivers())
            ->setAllRiders($this->getRiders());

        //See if we can generate a viable set of assignment
        if (!$trip_manager->assignDrivers()) {
            return false;
        }

        //If so, update the planning period by day accordingly & return it
        $this->updateDriverAssignmentsForPeriod($trip_manager->getTrips());

        //Return the fully assigned period
        return $this->getDays();
    }


    /**
     * @return array
     */
    private function getDays()
    {
        return $this->planning_days;
    }


    /**
     * Load full list of possible drivers & their details
     * @return array
     */
    private function getDrivers()
    {
        $driver_service = new DriverService();
        return $driver_service->getDrivers()->toArray();
    }


    /**
     * Load full list of possible riders & their details
     * @return array
     */
    private function getRiders()
    {
        $rider_service = new RiderService();
        return $rider_service->getRiders()->toArray();
    }


    /**
     * Flatten out the trips for ease, keeping a reference to their day for response
     */
    private function setTrips()
    {
        foreach ($this->planning_days as $day_key => $day) {
            foreach (data_get($day, 'trips', []) as $trip_key => $trip) {
                $trip['trip_key'] = $trip_key;
                $trip['day_key'] = $day_key;
                $this->trips[] = $trip;
            }
        }
    }


    /**
     * For every assigned trip, add the driver assignment to the appropriate day/trip
     * @param $assigned_trips
     */
    private function updateDriverAssignmentsForPeriod($assigned_trips)
    {
        foreach ($assigned_trips as $trip) {
           $this->planning_days[data_get($trip, 'day_key')]['trips'][data_get($trip, 'trip_key')]['driver_id'] = data_get($trip, 'driver_id');
        }
    }
}