<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Add2ColumnsInLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(env('PREFIX_TABLE') . 'logs', function (Blueprint $table) {
            $table->text('value_before')->nullable()->comment('JSON format')->after('note');
            $table->text('value_after')->nullable()->comment('JSON format')->after('value_before');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(env('PREFIX_TABLE') . 'logs', function (Blueprint $table) {
            //
        });
    }
}
