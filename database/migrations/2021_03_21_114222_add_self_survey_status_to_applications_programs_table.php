<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSelfSurveyStatusToApplicationsProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('applications_programs', function (Blueprint $table) {
            $table->boolean('self_survey_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applications_programs', function (Blueprint $table) {
            $table->dropColumn('self_survey_status');
        });
    }
}
