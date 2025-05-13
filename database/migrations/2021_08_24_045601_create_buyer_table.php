<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->string('email', 100);
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('token')->nullable();
            $table->boolean('status')->default(0);
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buyer');
    }
}
