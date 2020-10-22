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
            $table->integer('level');
            $table->date('preferred_date');
            $table->string('ppp');
            $table->string('compliance_report');
            $table->string('narative_report');
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
