<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignedUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assigned_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('instruments_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('app_program_id');
            $table->foreign('app_program_id')->references('id')->on('applications_programs')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate( 'cascade' )->onDelete( 'cascade' );
            $table->string('role');
            $table->binary('sfr_report')->nullable();
            $table->binary('sar_report')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('assigned_users');
    }
}
