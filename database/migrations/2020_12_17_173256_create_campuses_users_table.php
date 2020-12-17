<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampusesUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campuses_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id');
            $table->foreign("campus_id")->references("id")->on("campuses")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('user_id');
            $table->foreign("user_id")->references("id")->on("users")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('department')->nullable();
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
        Schema::dropIfExists('campuses_users');
    }
}
