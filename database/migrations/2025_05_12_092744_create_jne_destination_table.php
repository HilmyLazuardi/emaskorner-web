<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJneDestinationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jne_destination', function (Blueprint $table) {
            $table->id();
            $table->string('country_name', 100);
            $table->string('province_name', 100);
            $table->string('city_name', 100);
            $table->string('district_name', 100);
            $table->string('subdistrict_name', 100);
            $table->string('zip_code', 25);
            $table->string('tariff_code', 25);
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
        Schema::dropIfExists('jne_destination');
    }
}
