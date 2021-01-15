<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class  CreateAttachedDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attached_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statement_id');
            $table->foreign('statement_id')->references('id')->on('programs_statements')
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
        Schema::dropIfExists('attached_documents');
    }
}
