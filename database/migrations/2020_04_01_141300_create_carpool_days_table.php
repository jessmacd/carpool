<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpoolDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carpool_days', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('carpool_id')->unsigned();
            $table->string('day_label')->nullable();

            $table->timestamps();
            $table->foreign('carpool_id')->references('id')->on('carpools')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carpool_days');
    }
}
