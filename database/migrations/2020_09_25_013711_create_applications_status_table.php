<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_status', function (Blueprint $table) {
            $table->foreignId('application_id');
            $table->foreign('application_id')->references('id')->on('applications')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('program_id');
            $table->foreign('program_id')->references('id')->on('programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('result');
            $table->date('validity');
            $table->date('date_granted');
            $table->binary('certificate');
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
        Schema::dropIfExists('applications_status');
    }
}
