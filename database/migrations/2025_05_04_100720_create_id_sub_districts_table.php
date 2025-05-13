<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdSubDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('id_sub_districts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->tinyInteger('code');
            $table->string('full_code');
            $table->foreignId('kabupaten_id')->index('FK_id_cities');
            $table->timestamps();
        });

        $seeder = new SubDistrictSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('id_sub_districts');
    }
}
