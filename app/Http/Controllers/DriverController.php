<?php

namespace App\Http\Controllers;

use App\Services\Driver\DriverService;

class DriverController
{
    /**
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function index()
    {
        $service = app()->make(DriverService::class);
        return response()->json($service->getDrivers());
    }
}