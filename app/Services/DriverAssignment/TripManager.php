<?php

namespace App\Services\DriverAssignment;

use App\Services\DriverAssignment\AssignmentRules\ChildIsRidingRule;
use App\Services\DriverAssignment\AssignmentRules\DriverAssignmentRuleInterface;
use App\Services\DriverAssignment\AssignmentRules\DriverCapacityRule;
use App\Services\DriverAssignment\AssignmentRules\DriverConflictRule;
use App\Services\DriverAssignment\AssignmentRules\LimitTripsPerDayRule;
use Illuminate\Support\Collection;

class TripManager
{
    /**
     * @var Collection
     */
    private $trips;

    /**
     * @var Collection
     */
    private $drivers;

    /**
     * @var Collection
     */
    private $riders;

    /**
     * @var array
     */
    private static $driver_assignment_rules = [
        DriverConflictRule::class,
        ChildIsRidingRule::class,
        LimitTripsPerDayRule::class,
        DriverCapacityRule::class
    ];

    /**
     * @param $trips
     * @return $this
     */
    public function setTrips($trips)
    {
        //Give each trip a unique key and an order so we can restore the original order later.  Unset the driver  (this could be a parameter)
        $key_count = 1;
        $trips = collect($trips)->map(function($trip) use (&$key_count) {
            $trip['key'] = $key_count;
            $trip['driver'] = null;
            $key_count ++;
            return $trip;
        });

        //Shuffle the trips so that we don't get the same results every time we do assignments
        $this->trips = $trips->shuffle()->keyBy('key');
        return $this;
    }

    /**
     * Get the calculated trips
     * @return array
     */
    public function getTrips() {
        return $this->trips->sortBy('key')->toArray();
    }


    /**
     *  For now, we will put the drivers in a random order.
     *  We could also put the most conflicted drivers first to improve algorithm speed.
     *  Ultimately we would want to look at carpool history to put our drivers first who are "due" for a heavier load
     * @param $drivers
     * @return $this
     */
    public function setAllDrivers($drivers)
    {
        $this->drivers = collect($drivers)->shuffle()->keyBy('id');
        return $this;
    }

    /**
     * @param $riders
     * @return $this
     */
    public function setAllRiders($riders)
    {
        $this->riders = collect($riders)->keyBy('id');
        return $this;
    }


    /**
     * @return mixed
     */
    public function assignDrivers()
    {
        //The logic itself should prevent an infinite loop but lets be super safe but putting an upper limit on this loop
        $attempts = 0;
        $max_attempts = gmp_fact(sizeof($this->trips));


        //As long as we have unassigned trips
        while($this->tripsNeedAssignment() && $attempts < $max_attempts) {

            //Go driver by driver to ensure even assignment distribution
            foreach ($this->drivers as $driver) {

                //Ensure there are still unassigned trips
                if ($this->tripsNeedAssignment()) {

                    //Try to give the next driver a trip
                    $success = $this->assignNextTripToDriver($driver);

                    //If we couldn't, but we can skip them and still have a fair distribution...move on to the next driver
                    if (!$success && !$this->canDriverBeSkipped()) {
                        return false;
                    }

                    $attempts++;
                }
            }
        }

        //If there are no unassigned trips, we were successful
        return !$this->tripsNeedAssignment();
    }


    /**
     * Find an unassigned, valid trip for a given driver
     * @param $driver
     * @return bool
     */
    private function assignNextTripToDriver($driver)
    {
        //Look at available trips until we find one the driver is eligible for, then assign it
        $trips = $this->getUnassignedTrips();
        $driver_id = data_get($driver, 'id');

        $trip = $trips->first(function($trip) use ($driver_id ) {
            return $this->driverIsEligibleForTrip($trip, $driver_id );
        });

        //If we found an open trip this driver can take, give it to them
        if ($trip) {
            $this->setDriverOnTrip($trip, $driver_id);
            return true;

        } else {
            //If not, see if this driver can swap with an existing assignment
            if ($this->trySwap($driver_id, $trips->first())) {
                return true;
            }

            //No swaps means we are stuck
            return false;
        }
    }


    /**
     * Make an assignment of a driver to a trip
     * @param $trip
     * @param $driver_id
     */
    private function setDriverOnTrip($trip, $driver_id)
    {
        $updated_trip = $trip;
        $updated_trip['driver_id'] = $driver_id;
        $this->trips->put(data_get($trip, 'key'), $updated_trip);
    }


    /**
     * @param $driver_id
     * @param $swap_trip
     * @return bool
     */
    private function trySwap($driver_id, $swap_trip)
    {
        //Find all trips that are assigned, but not to this driver
        $possible_swaps = $this->getPossibleSwapTrips($driver_id);

        //If any of those drivers are eligible for the swap trip AND this driver is eligible for THEIR trip, perform a swap
        foreach ($possible_swaps as $possible_trip) {

            //Would our problem driver be eligible to take it?
            if ($this->driverIsEligibleForTrip($possible_trip, $driver_id)) {

                //And could its driver take this one?
                $possible_driver_id = data_get($possible_trip, 'driver_id');

                //We need to temporarily take them off their assigned trip to test whether the swap will work
                $context_trips = $this->trips;
                unset($possible_trip['driver_id']);
                $context_trips->put(data_get($possible_trip, 'key'), $possible_trip);

                if ($this->driverIsEligibleForTrip($swap_trip, $possible_driver_id, $context_trips )) {

                    //If both directions work,  we execute the swap, assigning out both the swapped trip and the new trip
                    $this->setDriverOnTrip($swap_trip, $possible_driver_id);
                    $this->setDriverOnTrip($possible_trip, $driver_id);
                    return true;
                }
            }
        }

        //We found no valid swaps
        return false;
    }


    /**
     * driver can be skipped if there are less open trips than under-assigned drivers
     * @return boolean
     */
    private function canDriverBeSkipped()
    {
        $remaining_trip_count = $this->getUnassignedTrips()->count();

        //How many under-assigned drivers do we have (what is the least number of trips, and then how many have that number)
        $counts_by_driver =collect($this->getAssignedTrips())->groupBy('driver_id')->map(function($item) {
            return collect($item)->count();
        });
        $min = $counts_by_driver->min();

        $assignable_drivers = $counts_by_driver->filter(function($value) use ($min) {
            return $value == $min;
        })->count();

        return ($assignable_drivers > $remaining_trip_count);
    }


    /**
     * @param $trip
     * @param $driver_id
     * @param null $context_trips
     * @return bool
     */
    private function driverIsEligibleForTrip($trip, $driver_id, $context_trips = null)
    {
        //Get all rules being enforced
        foreach ($this->getAssignmentRules() as $rule_class) {

            //Only run rules that are properly structured
            $rule = new $rule_class;

            if ($rule instanceOf DriverAssignmentRuleInterface) {

                $context = [
                    'all_trips' => ($context_trips) ? $context_trips : $this->trips,
                    'all_riders' => $this->riders,
                    'all_drivers' => $this->drivers
                ];

                //Any failed rule means the assignment fails and driver is not eligible
                if (!$rule->driverPassesRuleForTrip($trip, $driver_id, $context)) return false;
            }
        }
        return true;
    }


    /**
     * @return Collection
     */
    private function getUnassignedTrips()
    {
        return collect($this->trips)->filter(function($trip) {
            return !data_get($trip, 'driver_id');
        });
    }


    /**
     * @return Collection
     */
    private function getAssignedTrips() {
        return collect($this->trips)->filter(function($trip) {
            return data_get($trip, 'driver_id');
        });
    }


    /**
     * @param null $driver_needing_swap
     * @return Collection
     */
    private function getPossibleSwapTrips($driver_needing_swap) {
        return collect($this->trips)->filter(function($trip) use ($driver_needing_swap){
            $assigned_driver = data_get($trip, 'driver_id');
            return $assigned_driver && $assigned_driver != $driver_needing_swap;
        });
    }


    /**
     * @return bool
     */
    private function tripsNeedAssignment()
    {
         return $this->getUnassignedTrips()->count() > 0;
    }


    /**
     * Just returning a hard coded list of rules for now, but this gives us a structure
     * to allow configurable rules per carpool group, per planning period, etc. in the real world
     * @return array
     */
    private function getAssignmentRules()
    {
        return self::$driver_assignment_rules;
    }
}