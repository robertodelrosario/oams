<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametersProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parameters_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_instrument_id');
            $table->foreign('program_instrument_id')->references('id')->on('instruments_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('parameter_id');
            $table->foreign('parameter_id')->references('id')->on('parameters')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->float('acceptable_score_gap')->nullable();
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
        Schema::dropIfExists('parameters_programs');
    }
}
