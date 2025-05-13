<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyerAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buyer_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index('FK_buyer');
            $table->foreignId('province_code')->index('FK_province');
            $table->foreignId('district_code')->index('FK_district');
            $table->foreignId('sub_district_code')->index('FK_sub_district');
            $table->foreignId('village_code')->index('FK_village');
            $table->bigInteger('postal_code');
            $table->text('address_details')->nullable();
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
        Schema::dropIfExists('buyer_address');
    }
}
