<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersSucsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_sucs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suc_id');
            $table->foreign("suc_id")->references("id")->on("sucs")
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
        Schema::dropIfExists('users_sucs');
    }
}
