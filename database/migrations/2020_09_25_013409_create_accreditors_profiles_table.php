<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccreditorsProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accreditors_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('campus_id')->nullable();
            $table->foreign('campus_id')->references('id')->on('campuses')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('middle_initial')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthday')->nullable();
            $table->string('academic_rank')->nullable();
            $table->string('designation')->nullable();
            $table->string('region');
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('address')->nullable();
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
        Schema::dropIfExists('accreditors_profiles');
    }
}
