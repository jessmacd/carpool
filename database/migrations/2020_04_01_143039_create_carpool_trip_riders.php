<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpoolTripRiders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carpool_trip_riders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('carpool_trip_id')->unsigned();
            $table->bigInteger('rider_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('carpool_trip_id')->references('id')->on('carpool_trips')->onDelete('cascade');;
            $table->foreign('rider_id')->references('id')->on('riders')->onDelete('set null');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carpool_trip_riders');
    }
}
