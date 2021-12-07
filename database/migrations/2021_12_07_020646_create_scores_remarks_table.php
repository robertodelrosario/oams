<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoresRemarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scores_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_program_id');
            $table->foreign('application_program_id')->references('id')->on('applications_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('program_statement_id');
            $table->foreign('program_statement_id')->references('id')->on('programs_statements')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('sender_id');
            $table->foreign('sender_id')->references('id')->on('users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->text('message');
            $table->text('status');
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
        Schema::dropIfExists('scores_remarks');
    }
}
