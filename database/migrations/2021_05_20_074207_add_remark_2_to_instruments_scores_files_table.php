<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemark2ToInstrumentsScoresFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instruments_scores', function (Blueprint $table) {
            $table->string('remark_2')->nullable();
            $table->string('remark_2_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instruments_scores', function (Blueprint $table) {
            $table->dropColumn('remark_2');
            $table->dropColumn('remark_2_type');
        });
    }
}
