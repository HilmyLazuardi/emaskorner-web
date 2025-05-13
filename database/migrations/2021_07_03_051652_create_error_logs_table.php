<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'error_logs', function (Blueprint $table) {
            $table->id();
            $table->text('url_get_error')->nullable();
            $table->text('url_prev')->nullable();
            $table->text('err_message');
            $table->foreignId('admin_id')->index('FK_admins')->nullable();
            $table->foreignId('module_id')->index('FK_modules')->nullable();
            $table->foreignId('target_id')->index('FK_target')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(0)->comment('0:still error | 1:solved');
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
        Schema::dropIfExists(env('PREFIX_TABLE') . 'error_logs');
    }
}
