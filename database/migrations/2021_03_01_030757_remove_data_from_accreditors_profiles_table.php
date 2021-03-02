<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveDataFromAccreditorsProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accreditors_profiles', function (Blueprint $table) {
            $table->dropColumn(['middle_initial', 'gender', 'birthday', 'province', 'municipality', 'address']);
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
            //
        });
    }
}
