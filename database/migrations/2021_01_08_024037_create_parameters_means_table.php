<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametersMeansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parameters_means', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_parameter_id');
            $table->foreign('program_parameter_id')->references('id')->on('parameters_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('accreditor_id');
            $table->foreign('accreditor_id')->references('id')->on('users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->float('parameter_mean');
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
        Schema::dropIfExists('parameters_means');
    }
}
