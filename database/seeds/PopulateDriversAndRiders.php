<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class PopulateDriversAndRiders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $drivers = [
            [
                'name' => 'Jessica',
                'capacity' => 6,
                'riders' => [
                    ['name' => 'Kaitlyn']
                ]
            ],
            [
                'name' => 'Lois',
                'capacity' => 6,
                'riders' => [
                    ['name' => 'Austin']
                ]
            ],
            [
                'name' => 'Angie',
                'capacity' => 4,
                'riders' => [
                    ['name' => 'Jaz']
                ]
            ],
            [
                'name' => 'Steve',
                'capacity' => 4,
                'riders' => [
                    ['name' => 'Libby']
                ]
            ]
        ];

        //Insert each driver and their associated child or children as riders
        //@todo: replace with models
        foreach ($drivers as $driver) {

            //First the driver
            $driver_id = DB::table('drivers')->insertGetId(Arr::only($driver, ['name', 'capacity']));

            //Then any children who are riders
            foreach (data_get($driver, 'riders', []) as $rider) {
                $rider['parent_driver_id'] = $driver_id;
                DB::table('riders')->insert($rider);
            }
        }
    }
}
