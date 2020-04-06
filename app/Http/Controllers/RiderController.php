<?php

namespace App\Http\Controllers;

use App\Services\Rider\RiderService;

class RiderController
{
    /**
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function index()
    {
        $service = app()->make(RiderService::class);
        return response()->json($service->getRiders());
    }
}