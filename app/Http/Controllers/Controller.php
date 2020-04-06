<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    const STATUS_CODE_SUCCESS = 200;
    const STATUS_CODE_FAILED = 400;
    const CODE_FAILED_VALIDATION = 'VALIDATION';
    const CODE_TRIPS_UNASSIGNABLE = 'TRIPS_UNASSIGNABLE';

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param $error_code
     * @param $error_messages
     * @return array
     */
    protected function buildFailedResponse($error_code=400, $error_messages = null)
    {
        return [
            'error_code' => $error_code,
            'errors' => $error_messages
        ];
    }
}
