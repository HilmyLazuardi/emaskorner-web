<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerProviderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_provider', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index('FK_buyer');
            $table->string('provider_name', 100)->comment('google or facebook');
            $table->string('token')->comment('token from google or facebook');
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
        Schema::dropIfExists('buyer_provider');
    }
}
