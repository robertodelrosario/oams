<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId("suc_id");
            $table->foreign("suc_id")->references("id")->on("sucs")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('campus_name');
            $table->string('address');
            $table->string('region');
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('email');
            $table->string('contact_no');
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
        Schema::dropIfExists('campuses');
    }
}
