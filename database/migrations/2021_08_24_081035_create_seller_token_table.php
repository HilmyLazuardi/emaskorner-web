<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_token', function (Blueprint $table) {
            $table->id();
            $table->string('purpose', 100)->comment('agreement/forget password');
            $table->foreignId('user_id')->index('FK_seller');
            $table->string('token')->comment('must be unique');
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
        Schema::dropIfExists('seller_token');
    }
}
