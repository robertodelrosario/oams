<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id');
            $table->foreign('notification_id')->references('id')->on('notifications')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('applied_program_id');
            $table->foreign('applied_program_id')->references('id')->on('applications_programs')
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
        Schema::dropIfExists('notifications_programs');
    }
}
