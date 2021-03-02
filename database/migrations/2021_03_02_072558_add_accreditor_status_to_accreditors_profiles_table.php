<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccreditorStatusToAccreditorsProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accreditors_profiles', function (Blueprint $table) {
            $table->string('accreditor_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accreditors_profiles', function (Blueprint $table) {
            $table->dropColumn('accreditor_status');
        });
    }
}
