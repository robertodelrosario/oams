<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id');
            $table->foreign('recipient_id')->references('id')->on('users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('sender_id');
            $table->foreign('sender_id')->references('id')->on('users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('notification_id');
            $table->foreign('notification_id')->references('id')->on('notification_contents')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->boolean('status');
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
        Schema::dropIfExists('notifications');
    }
}
