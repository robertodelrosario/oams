<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBestPracticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('best_practices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_parameter_id');
            $table->foreign('program_parameter_id')->references('id')->on('parameters_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('assigned_user_id');
            $table->foreign('assigned_user_id')->references('id')->on('assigned_users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('best_practice');
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
        Schema::dropIfExists('best_practices');
    }
}
