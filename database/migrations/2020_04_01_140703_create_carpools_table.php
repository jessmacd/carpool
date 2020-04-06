<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carpools', function (Blueprint $table) {
            $table->id();

            $table->timestamp('begins_at')->nullable();  //In reality would not be nullable
            $table->timestamp('ends_at')->nullable();  //In reality would not be nullable
            $table->bigInteger('carpool_group_id')->unsigned()->default(1); //This is a placeholder for now, but in the future would be a FK
            $table->boolean('planning_open')->default(1);

            $table->timestamps();

            $table->index('carpool_group_id');  //Will become important in real-world for performance
            $table->index('planning_open');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carpools');
    }
}
