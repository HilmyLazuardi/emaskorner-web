<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->string('password')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('identity_number')->nullable();
            $table->string('identity_image')->nullable();
            $table->string('npwp_number')->nullable();
            $table->foreignId('province_code')->index('FK_province');
            $table->foreignId('district_code')->index('FK_district');
            $table->foreignId('sub_district_code')->index('FK_sub_district');
            $table->foreignId('village_code')->index('FK_village');
            $table->integer('postal_code');
            $table->boolean('approval_status')->default(false);
            $table->boolean('status')->default(true);
            $table->dateTime('approved_at')->nullable();
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
        Schema::dropIfExists('seller');
    }
}
