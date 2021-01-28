<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationsProgramsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications_programs_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_program_id');
            $table->foreign('application_program_id')->references('id')->on('applications_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('uploader_id')->nullable();
            $table->foreign("uploader_id")->references("id")->on("users")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('file_title');
            $table->string('file');
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
        Schema::dropIfExists('applications_programs_files');
    }
}
