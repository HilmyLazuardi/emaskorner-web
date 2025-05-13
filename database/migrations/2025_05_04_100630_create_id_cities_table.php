<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('id_cities', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->tinyInteger('code');
            $table->string('full_code');
            $table->foreignId('provinsi_id')->index('FK_id_provinces');
            $table->timestamps();
        });

        $seeder = new DistrictSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('id_cities');
    }
}
