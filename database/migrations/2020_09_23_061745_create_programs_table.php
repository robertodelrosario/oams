<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string("program_name");
            $table->float('rating_obtained')->nullable();
            $table->string("accreditation_status");
            $table->string("latest_applied_level")->nullable();
            $table->date("duration_of_validity");
            $table->foreignId("campus_id");
            $table->foreign("campus_id")->references("id")->on("campuses")
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
        Schema::dropIfExists('programs');
    }
}
