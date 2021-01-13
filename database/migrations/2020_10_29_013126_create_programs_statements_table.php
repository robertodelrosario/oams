<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramsStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_parameter_id');
            $table->foreign('program_parameter_id')->references('id')->on('parameters_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('benchmark_statement_id');
            $table->foreign('benchmark_statement_id')->references('id')->on('benchmark_statements')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId("parent_statement_id")->nullable();
            $table->foreign("parent_statement_id")->references("id")->on("benchmark_statements")
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
        Schema::dropIfExists('programs_statements');
    }
}
