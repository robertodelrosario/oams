<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePppStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppp_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_parameter_id');
            $table->foreign('program_parameter_id')->references('id')->on('parameters_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->text('statement');
            $table->string('type');
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
        Schema::dropIfExists('ppp_statements');
    }
}
