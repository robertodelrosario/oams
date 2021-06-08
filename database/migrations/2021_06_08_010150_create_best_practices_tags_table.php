<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBestPracticesTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('best_practices_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('best_practice_office_id')->nullable();
            $table->foreign("best_practice_office_id")->references("id")->on("best_practices_offices")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('tag');
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
        Schema::dropIfExists('best_practices_tags');
    }
}
