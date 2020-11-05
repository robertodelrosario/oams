<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametersStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parameters_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId("parameter_id");
            $table->foreign("parameter_id")->references("id")->on("parameters")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId("benchmark_statement_id");
            $table->foreign("benchmark_statement_id")->references("id")->on("benchmark_statements")
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
        Schema::dropIfExists('parameters_statements');
    }
}
