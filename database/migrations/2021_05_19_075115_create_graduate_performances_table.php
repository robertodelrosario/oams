<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraduatePerformancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('graduate_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_statement_id');
            $table->foreign('program_statement_id')->references('id')->on('programs_statements')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('year');
            $table->float('rating');
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
        Schema::dropIfExists('graduate_performances');
    }
}
