<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaInstrumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId("program_id");
            $table->foreign("program_id")->references("id")->on("programs")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->integer("area_number");
            $table->string('area_name');
            $table->string("version");
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
        Schema::dropIfExists('area_instruments');
    }
}
