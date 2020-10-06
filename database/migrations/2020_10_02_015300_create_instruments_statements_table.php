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
            $table->foreignId("area_instrument_id");
            $table->foreign("area_instrument_id")->references("id")->on("area_instruments")
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
        Schema::dropIfExists('instruments_statements');
    }
}
