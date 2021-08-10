<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasMandatoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas_mandatory', function (Blueprint $table) {
            $table->id();
            $table->foreignId("area_instrument_id");
            $table->foreign("area_instrument_id")->references("id")->on("area_instruments")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string("level");
            $table->string("type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas_mandatory');
    }
}
