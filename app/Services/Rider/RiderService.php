<?php

namespace App\Services\Rider;

use App\Services\Rider\Data\Rider;
use Illuminate\Support\Collection;

class RiderService
{
    /**
     * @return Collection
     */
    public function getRiders() {
        return Rider::get();
    }
}