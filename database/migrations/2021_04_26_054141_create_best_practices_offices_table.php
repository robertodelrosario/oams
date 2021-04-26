<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBestPracticesOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('best_practices_offices', function (Blueprint $table) {
            $table->id();
            $table->string('best_practice');
            $table->foreignId('user_id')->nullable();
            $table->foreign("user_id")->references("id")->on("users")
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('office_id')->nullable();
            $table->foreign("office_id")->references("id")->on("offices")
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
        Schema::dropIfExists('best_practices_offices');
    }
}
