<?php

namespace App\Http\Controllers;

use App\Services\DriverAssignment\DriverAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DriverAssignmentController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function assignDrivers(Request $request)
    {
        $service = app()->make(DriverAssignmentService::class);

        //validate the the input
        $validator = Validator::make($request->all(), $service->getAssignmentRules());

        if($validator->fails()){
            $status_code = self::STATUS_CODE_FAILED;
            $response = $this->buildFailedResponse(self::CODE_FAILED_VALIDATION, $validator->errors()->getMessages());

        } else {

            $assignments = $service->assignDrivers($request->get('days'));
            if (!$assignments) {

                ///If we want to track and make available specific failure reasons we could return them here (i..e. failed to make even distribution, unresolvable conflicts, etc)
                $response = $this->buildFailedResponse(self::CODE_TRIPS_UNASSIGNABLE);
                $status_code = self::STATUS_CODE_FAILED;

            } else {
                $response = ['days' => $assignments];
                $status_code = self::STATUS_CODE_SUCCESS;
            }
        }


        return response()->json($response)->setStatusCode($status_code);
    }
}