<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourierJneLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courier_jne_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10)->nullable()->comment('POST/GET');
            $table->string('endpoint', 255)->nullable();
            $table->text('headers')->nullable();
            $table->string('params_type', 100)->nullable()->comment('form_params/json');
            $table->text('params')->nullable();
            $table->text('response')->nullable();
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
        Schema::dropIfExists('courier_jne_logs');
    }
}
