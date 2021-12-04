<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationCoordinatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_coordinators', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id");
            $table->foreign("user_id")->references("id")->on("users")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId("application_id");
            $table->foreign("application_id")->references("id")->on("applications")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('status');
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
        Schema::dropIfExists('application_coordinators');
    }
}
