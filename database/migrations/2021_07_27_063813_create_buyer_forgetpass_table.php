<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerForgetpassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_forgetpass', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index('FK_buyer');
            $table->string('token')->comment('must be unique, build it with combination from email and datetime(Y-m-d H:i:s))');
            $table->dateTime('expired_at');
            $table->boolean('status')->comment('0:unused | 1:used, update this field when already success reset password');
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
        Schema::dropIfExists('buyer_forgetpass');
    }
}
