<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrumentsStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruments_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId("instrument_parameter_id");
            $table->foreign("instrument_parameter_id")->references("id")->on("instruments_parameters")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId("benchmark_statement_id");
            $table->foreign("benchmark_statement_id")->references("id")->on("benchmark_statements")
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
        Schema::dropIfExists('instruments_statements');
    }
}
