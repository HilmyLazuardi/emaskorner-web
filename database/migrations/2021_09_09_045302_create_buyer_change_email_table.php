<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerChangeEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_change_email', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index('FK_buyer');
            $table->string('user_new_email');
            $table->string('token')->comment('must be unique');
            $table->tinyInteger('retry')->default(0);
            $table->dateTime('expired_at');
            $table->boolean('status')->comment('0:unused | 1:used');
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
        Schema::dropIfExists('buyer_change_email');
    }
}
