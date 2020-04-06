<?php

namespace App\Services\Carpool;

use App\Services\Carpool\Data\CarpoolRepository;
use App\Services\Driver\DriverService;
use App\Services\Rider\RiderService;
use Illuminate\Support\Facades\Validator;

class CarpoolService
{
    /**
     * Store a NEW carpool, it trips and all their associated data
     * @param $request_data
     * @return mixed
     */
    public function saveCarpool($request_data)
    {
        $repository = new CarpoolRepository();
        return $repository->saveCarpool($request_data);
    }


    /**
     * @return array
     */
    public function getValidationRules()
    {
        //Add custom rules
        $this->defineValidDriverRule();
        $this->defineValidRiderRule();

        //Return rules
        return [
            'days'                              => 'required|array',
            'days.*.trips'                      => 'required|array',
            'days.*.trips.*.riders'             => 'required|array',
            'days.*.trips.*.riders.*'           => 'required|rider_is_valid',
            'days.*.trips.*.driver_conflicts'   => 'sometimes|array',
            'days.*.trips.*.driver_conflicts.*' => 'sometimes|driver_is_valid'
        ];
    }


    /**
     * Set up a custom rule for validating drivers (future would include that they belong to carpool group)
     *   (in its current form, could be accomplished with the 'exists' rule, but that wouldn't be enough if this were built out fully)
     */
    private function defineValidDriverRule()
    {
        $driver_service = new DriverService();
        $valid_driver_ids = $driver_service->getDrivers()->pluck('id')->toArray();

        Validator::extend('driver_is_valid', function ($attribute, $value, $parameters, $validator) use($valid_driver_ids) {
            return in_array($value, $valid_driver_ids);
        });
    }


    /**
     * Set up a custom rule for validating riders (future would include that they belong to carpool group)
     *   (in its current form, could be accomplished with the 'exists' rule, but that wouldn't be enough if this were built out fully)
     */
    private function defineValidRiderRule()
    {
        $rider_service = new RiderService();
        $valid_rider_ids = $rider_service->getRiders()->pluck('id')->toArray();

        Validator::extend('rider_is_valid', function ($attribute, $value, $parameters, $validator) use($valid_rider_ids) {
            return in_array($value, $valid_rider_ids);
        });
    }
}