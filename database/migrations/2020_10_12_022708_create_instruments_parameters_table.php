<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrumentsParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruments_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId("area_instrument_id");
            $table->foreign("area_instrument_id")->references("id")->on("area_instruments")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId("parameter_id");
            $table->foreign("parameter_id")->references("id")->on("parameters")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
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
        Schema::dropIfExists('instruments_parameters');
    }
}
