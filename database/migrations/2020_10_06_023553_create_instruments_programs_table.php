<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrumentsProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruments_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId("program_id");
            $table->foreign("program_id")->references("id")->on("programs")
                ->onUpdate( 'cascade' )->onDelete( 'restrict' );
            $table->foreignId("area_instrument_id");
            $table->foreign("area_instrument_id")->references("id")->on("area_instruments")
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
        Schema::dropIfExists('instruments_programs');
    }
}
