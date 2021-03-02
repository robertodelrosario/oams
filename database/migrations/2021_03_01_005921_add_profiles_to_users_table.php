<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfilesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('current_address')->nullable();
            $table->string('home_status')->nullable();
            $table->string('telephone_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('birthdate')->nullable();
            $table->dropColumn('gender')->nullable();
            $table->dropColumn('civil_status')->nullable();
            $table->dropColumn('place_of_birth')->nullable();
            $table->dropColumn('nationality')->nullable();
            $table->dropColumn('current_address')->nullable();
            $table->dropColumn('home_status')->nullable();
            $table->dropColumn('telephone_no')->nullable();
        });
    }
}
