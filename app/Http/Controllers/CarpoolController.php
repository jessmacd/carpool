<?php

namespace App\Http\Controllers;
use App\Services\Carpool\CarpoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarpoolController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function store(Request $request)
    {
        $service = app()->make(CarpoolService::class);

        //validate the the input
        $validator = Validator::make($request->all(), $service->getValidationRules());

        if($validator->fails()){
            $status_code = self::STATUS_CODE_FAILED;
            $response = $this->buildFailedResponse(self::CODE_FAILED_VALIDATION, $validator->errors()->getMessages());

        } else {
            $response = $service->saveCarpool($request->all());
            $status_code = self::STATUS_CODE_SUCCESS;
        }

        return response()->json($response)->setStatusCode($status_code);
    }



}