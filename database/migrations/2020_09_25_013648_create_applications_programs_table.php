<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->foreign('application_id')->references('id')->on('applications')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('program_id');
            $table->foreign('program_id')->references('id')->on('programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('level');
            $table->date('preferred_start_date');
            $table->date('preferred_end_date');
            $table->date('approved_start_date')->nullable();
            $table->date('approved_end_date')->nullable();
            $table->string('status')->nullable();
            $table->string('result')->nullable();
            $table->date('date_granted')->nullable();
            $table->string('certificate')->nullable();
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
        Schema::dropIfExists('applications_programs');
    }
}
