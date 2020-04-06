<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpoolTripConflicts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carpool_trip_conflicts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('carpool_trip_id')->unsigned();
            $table->bigInteger('driver_id')->unsigned();
            $table->timestamps();

            $table->foreign('carpool_trip_id')->references('id')->on('carpool_trips')->onDelete('cascade');;
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carpool_trip_conflicts');
    }
}
