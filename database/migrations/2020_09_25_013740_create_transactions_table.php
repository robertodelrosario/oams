<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_program_id');
            $table->foreign('application_program_id')->references('id')->on('applications_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('area_instrument_id');
            $table->foreign('area_instrument_id')->references('id')->on('area_instruments')
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
        Schema::dropIfExists('transactions');
    }
}
