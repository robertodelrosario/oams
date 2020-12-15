<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id');
            $table->foreign('application_id')->references('id')->on('applications')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('file_title');
            $table->string('file');
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
        Schema::dropIfExists('applications_files');
    }
}
