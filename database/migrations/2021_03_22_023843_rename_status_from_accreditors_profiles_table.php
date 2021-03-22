<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameStatusFromAccreditorsProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accreditors_profiles', function (Blueprint $table) {
            $table->renameColumn('status', 'suc_status');
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
            $table->renameColumn('suc_status', 'status');
        });
    }
}
