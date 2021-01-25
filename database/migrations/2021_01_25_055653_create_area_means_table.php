<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaMeansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_means', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_program_id');
            $table->foreign('instrument_program_id')->references('id')->on('instruments_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('assigned_user_id');
            $table->foreign('assigned_user_id')->references('id')->on('assigned_users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->float('area_mean');
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
        Schema::dropIfExists('area_means');
    }
}
