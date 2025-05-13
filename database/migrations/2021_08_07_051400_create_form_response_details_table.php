<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormResponseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'form_response_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->index();
            $table->foreignId('form_response_id')->index();
            $table->text('question');
            $table->text('answer');
            $table->text('other')->nullable()->comment('filled if option other choosen');
            $table->boolean('score')->default(false);
            $table->integer('point')->default(0);
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
        Schema::dropIfExists(env('PREFIX_TABLE') . 'form_response_details');
    }
}
