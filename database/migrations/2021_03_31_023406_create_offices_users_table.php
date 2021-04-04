<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficesUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offices_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable();
            $table->foreign("office_id")->references("id")->on("offices")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('user_role_id')->nullable();
            $table->foreign("user_role_id")->references("id")->on("users_roles")
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
        Schema::dropIfExists('offices_users');
    }
}
