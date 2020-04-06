<?php

namespace Tests\Feature;

use App\Services\DriverAssignment\TripManager;

use Tests\TestCase;

class TripManagerTest extends TestCase
{
    /**
     * @var TripManager
     */
    private $trip_manager;

    protected function setup():void {
        $this->trip_manager = new TripManager();
    }

    /**
     * Basic test of setting trips and getting the same trips out

     * @return void
     */
    public function testCanSetTrips()
    {
        $trip_manager = $this->setUpPlanningPeriod();
        $trips_out = $trip_manager->getTrips();
        $this->assertCount(4, $trips_out);
    }

    /**
     *  Test that a basic case of driver assignment works
     */
    public function testCanAssignDrivers()
    {
        //Very basic case of 4 trips, 4 drivers
        $trip_manager = $this->setUpPlanningPeriod();
        $success = $trip_manager->assignDrivers();
        $trips_out = $trip_manager->getTrips();
        $this->assertTrue($success);

        //We should get back the same number of trips we sent in
        $this->assertCount(4, $trips_out);

        //and each should have a driver
        $this->checkAllDriversAssigned($trips_out);
    }

    /**
     * With more trips than drivers, does assignment still work
     */
    public function testCanAssignDriversToMultipleTrips()
    {
        //4 days = 8 trips
        $trip_manager = $this->setUpPlanningPeriod(4);
        $success = $trip_manager->assignDrivers();
        $this->assertTrue($success);

        //We should get back the same number of trips we sent in
        $trips_out = $trip_manager->getTrips();
        $this->assertCount(8, $trips_out);

        //and each should have a driver
        $this->checkAllDriversAssigned($trips_out);
    }

    /**
     *  An uneven number of trips can be assigned to drivers.  e.g.  6 trips and 4 drivers means 2 will get assigned
     *     2 trips, and 2 will be assigned 1
     */
    public function testCanAssignDriverUnevenly()
    {
        //3 days = 6 trips
        $trip_manager = $this->setUpPlanningPeriod(3);
        $success = $trip_manager->assignDrivers();
        $this->assertTrue($success);

        //We should get back the same number of trips we sent in
        $trips_out = $trip_manager->getTrips();
        $this->assertCount(6, $trips_out);

        //and each should have a driver
        $this->checkAllDriversAssigned($trips_out);

        //And the driver with the most should have no more than 1 more than the driver with the least
        $this->checkDistribution($trips_out);
    }

    /**
     *  Drivers are not assigned a trip for which they have set a conflict  (not truly a unit test)
     */
    public function testDriverConflictsRespected() {
        //Set up a trip with two days (aka 4 trips)
        $trip_manager = $this->trip_manager;
        $sample_trips = $this->getSampleTrips(2);

        //Give driver 1 a conflict on 3 out of 4 days, meaning they can only be assigned to one specific day
        $busy_driver = 1;
        $allow_only_trip = 2;
        foreach($sample_trips as $key => $trip) {
            if ($key != $allow_only_trip-1) {
                $sample_trips[$key]['driver_conflicts'] = [$busy_driver];
            }
        }

        $trip_manager
            ->setAllDrivers($this->getDrivers())
            ->setAllRiders($this->getRiders());

        //This is not a true test at this level because of the randomness of assignment.  Need to add separate unit tests for each rule
        //However a failure here is still telling
        for ($tries=1; $tries <= 100; $tries++) {
            $success = $trip_manager->setTrips($sample_trips)->assignDrivers();
            $this->assertTrue($success);
            $trips_out = $trip_manager->getTrips();
            $this->checkDriverHasTrip($trips_out, $busy_driver, [$allow_only_trip]);
        }
    }

    /**
     *  Ensure a single driver isn't assigned multiple trips on the same day (not truly a unit test)
     */
    public function testNoTwoTripsOnSameDay() {
        //Set up a trip with 4 days (aka 8 trips)
        $trip_manager = $this->trip_manager;
        $sample_trips = $this->getSampleTrips(2);

        //Give driver 1 a conflict on half of them to increase odds they'll be assigned same day trips
        $busy_driver = 1;
        $allow_only_days = [0,1];
        foreach($sample_trips as $key => $trip) {
            $day_id = data_get($trip, 'day_key');
            if (!in_array($day_id, $allow_only_days)) {
                $sample_trips[$key]['driver_conflicts'] = [$busy_driver];
            }
        }

        $trip_manager
            ->setAllDrivers($this->getDrivers())
            ->setAllRiders($this->getRiders());

        //This is not a true test at this level because of the randomness of assignment.  Need to add separate unit tests for each rule
        //However a failure here is still telling
        for ($tries=1; $tries <= 100; $tries++) {
            $success = $trip_manager->setTrips($sample_trips)->assignDrivers();
            $this->assertTrue($success);
            $trips_out = $trip_manager->getTrips();
            $this->checkMaxOneTripPerDay($trips_out);
        }
    }

    /**
     * Test that we can create an unassignable carpool scenario, and that the response will indicate that (not truly a unit test)
     */
    public function testUnassignableCarpools() {
        //Set up a scenario where full assignment is not possible
        $trip_manager = $this->trip_manager;
        $sample_trips = $this->getSampleTrips(2);

        //Give all drivers a conflict on one of the trips
        $sample_trips[0]['driver_conflicts']  = [1,2,3,4];

        $trip_manager
            ->setAllDrivers($this->getDrivers())
            ->setAllRiders($this->getRiders());

        $success = $trip_manager->setTrips($sample_trips)->assignDrivers();
        $this->assertFalse($success);
    }


    /**
     *  Ensure we cannot assign a driver a trip when their child is not riding that trip (not truly a unit test)
     */
    public function testChildRidingRespected() {
        //Set up a trip with two days (aka 4 trips)
        $trip_manager = $this->trip_manager;
        $sample_trips = $this->getSampleTrips(2);

        //Child 1 (belonging to driver1) should only ride one one day, meaning they can only be assigned to one specific day
        $limited_driver = 1;
        $allow_only_trip = 2;
        foreach($sample_trips as $key => $trip) {
            if ($key != $allow_only_trip-1) {
                $sample_trips[$key]['riders'] = [2,3,4];
            }
        }

        $trip_manager
            ->setAllDrivers($this->getDrivers())
            ->setAllRiders($this->getRiders());

        //This is not a true test at this level because of the randomness of assignment.  Need to add separate unit tests for each rule
        //However a failure here is still telling
        for ($tries=1; $tries <= 100; $tries++) {
            $success = $trip_manager->setTrips($sample_trips)->assignDrivers();
            $this->assertTrue($success);
            $trips_out = $trip_manager->getTrips();

            //make sure our driver is assigned to their only possible trip
            $this->checkDriverHasTrip($trips_out, $limited_driver, [$allow_only_trip]);
        }
    }

    /**
     * Test that limitations on car capacity are enforced (not truly a unit test)
     */
    public function testCarCapacityRespected() {
        //Set up a trip with two days (aka 4 trips)
        $trip_manager = $this->trip_manager;
        $sample_trips = $this->getSampleTrips(2);

        //Give one driver a capacity of 3 and one trip 3 riders, meaning there is only one valid trip they could be assigned
        $limited_driver = 2;
        $drivers = $this->getDrivers();
        foreach ($drivers as $key => $driver) {
            if ($key == $limited_driver - 1) {
                $drivers[$key]['capacity'] = 3;
            }
        }

        $allow_only_trip = 2;
        foreach($sample_trips as $key => $trip) {
            if ($key == $allow_only_trip-1) {
                $sample_trips[$key]['riders'] = [1, 2, 3];
            }
        }

        $trip_manager
            ->setAllDrivers($drivers)
            ->setAllRiders($this->getRiders());

        //This is not a true test at this level because of the randomness of assignment.  Need to add separate unit tests for each rule
        //However a failure here is still telling
        for ($tries=1; $tries <= 100; $tries++) {
            $success = $trip_manager->setTrips($sample_trips)->assignDrivers();
            $this->assertTrue($success);
            $trips_out = $trip_manager->getTrips();

            //make sure our driver is assigned to their only possible trip
            $this->checkDriverHasTrip($trips_out, $limited_driver, [$allow_only_trip]);
        }
    }


    /**
     * Helper check to ensure all trips got a driver assigned
     * @param $trips
     */
    private function checkAllDriversAssigned($trips) {
        foreach ($trips as $trip) {
            $this->assertGreaterThan(0, data_get($trip, 'driver_id'));
        }
    }

    /**
     * @param $all_trips
     * @param $driver_id
     * @param array $expected_trips
     * @param bool $exclusive
     */
    private function checkDriverHasTrip($all_trips, $driver_id, $expected_trips = [], $exclusive = true) {
        foreach ($all_trips as $key => $trip) {
            $assigned_driver = data_get($trip, 'driver_id');

            if (in_array($key, $expected_trips)) {
                $this->assertEquals($driver_id, $assigned_driver);
            } else {
                if ($exclusive) {
                    $this->assertNotEquals($driver_id, $assigned_driver);
                }
            }
        }
    }


    /**
     * Check that trips are distributed evenly (or as evenly as possible in the case of uneven distribution.
     * @param $trips
     */
    private function checkDistribution($trips) {
        $trips_by_driver = collect($trips)->groupBy('driver_id');
        $counts_by_driver = $trips_by_driver->map(function($item) {
            return collect($item)->count();
        });

        $max_trips = $counts_by_driver->max();
        $min_trips = $counts_by_driver->min();

        //The driver with the most trips should the same as, or at most 1 more, than the driver with the least
        $this->assertLessThanOrEqual(1, $max_trips - $min_trips);
    }


    /**
     * No one has been given two trips on the same day
     * @param $trips
     */
    private function checkMaxOneTripPerDay($trips) {
        $trips_by_day= collect($trips)->groupBy('day_key');
        $trips_by_day->each(function($trips) {
            $day_drivers = $trips->pluck('driver_id')->toArray();
            $this->assertEquals(count($day_drivers), count(array_unique($day_drivers)));
        });

    }

    /**
     * Helper to set up a default planning period
     * @param int $days
     * @return TripManager
     */
    private function setUpPlanningPeriod($days=2) {
        $trip_manager = $this->trip_manager;
        $sample_trips = $this->getSampleTrips($days);

        return $trip_manager
            ->setTrips($sample_trips)
            ->setAllDrivers($this->getDrivers())
            ->setAllRiders($this->getRiders());

    }

    /**
     * Helper to build sample trip data
     * @param int $days
     * @return array
     */
    private function getSampleTrips($days = 2) {
        $trips = [];
        for ($i=0; $i<$days; $i++) {
            //Just add 2 trips per day for now
            $trips[]=['day_key' => $i, 'riders' => [1,2,3,4], 'driver_id' => null];
            $trips[]=['day_key' => $i, 'riders' => [1,2,3,4], 'driver_id' => null];
        }
        return $trips;
    }


    /**
     * An array of fake drivers
     * @return array
     */
    private function getDrivers() {
        return [
            ['id'=>1, 'name' => 'Driver 1'],
            ['id'=>2, 'name' => 'Driver 2'],
            ['id'=>3, 'name' => 'Driver 3'],
            ['id'=>4, 'name' => 'Driver 4']
        ];
    }

    /**
     * An array of fake riders linked to our fake drivers
     * @return array
     */
    private function getRiders() {
        return [
            ['id'=>1, 'name' => 'Rider 1', 'parent_driver_id' => 1],
            ['id'=>2, 'name' => 'Rider 2', 'parent_driver_id' => 2],
            ['id'=>3, 'name' => 'Rider 3', 'parent_driver_id' => 3],
            ['id'=>4, 'name' => 'Rider 4', 'parent_driver_id' => 4]
        ];
    }
}
