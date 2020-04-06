<?php

namespace App\Services\Driver;
use App\Services\Driver\Data\Driver;
use Illuminate\Support\Collection;

class DriverService
{
    /**
     * @return Collection
     */
    public function getDrivers()
    {
        $model = new Driver();
        return $model->get();
    }
}