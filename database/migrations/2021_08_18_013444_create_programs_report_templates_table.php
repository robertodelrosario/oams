<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramsReportTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs_report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_program_id');
            $table->foreign('instrument_program_id')->references('id')->on('instruments_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('report_template_id');
            $table->foreign('report_template_id')->references('id')->on('report_templates')
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
        Schema::dropIfExists('programs_report_templates');
    }
}
