<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccreditorRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accreditor_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_program_id');
            $table->foreign('application_program_id')->references('id')->on('applications_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('accreditor_id');
            $table->foreign('accreditor_id')->references('id')->on('users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('instrument_program_id');
            $table->foreign('instrument_program_id')->references('id')->on('instruments_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('status');
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
        Schema::dropIfExists('accreditor_requests');
    }
}
