<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserAgentInErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(env('PREFIX_TABLE') . 'error_logs', function (Blueprint $table) {
            $table->string('ip_address', 100)->nullable()->after('status');
            $table->text('user_agent')->nullable()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(env('PREFIX_TABLE') . 'error_logs', function (Blueprint $table) {
            //
        });
    }
}
