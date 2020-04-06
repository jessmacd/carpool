<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpoolTrips extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carpool_trips', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('carpool_day_id')->unsigned();
            $table->string('time'); //Future would make 'time' and actual time and add a string label separately
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('carpool_day_id')->references('id')->on('carpool_days')->onDelete('cascade');;
            $table->foreign('driver_id')->references('id')->on('carpool_days')->onDelete('set null');;

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carpool_trips');
    }
}
