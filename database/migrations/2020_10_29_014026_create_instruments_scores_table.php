<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrumentsScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instruments_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id');
            $table->foreign('item_id')->references('id')->on('programs_statements')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('assigned_user_id');
            $table->foreign('assigned_user_id')->references('id')->on('assigned_users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->float('item_score')->nullable();
            $table->text('remark')->nullable();
            $table->string('remark_type')->nullable();
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
        Schema::dropIfExists('instruments_scores');
    }
}
