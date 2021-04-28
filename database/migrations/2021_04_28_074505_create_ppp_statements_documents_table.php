<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePppStatementsDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppp_statements_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppp_statement_id');
            $table->foreign('ppp_statement_id')->references('id')->on('ppp_statements')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('document_id');
            $table->foreign('document_id')->references('id')->on('documents')
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
        Schema::dropIfExists('ppp_statements_documents');
    }
}
