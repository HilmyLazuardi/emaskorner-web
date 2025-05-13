<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'countries', function (Blueprint $table) {
            $table->id();
            $table->string('country_alias', 2);
            $table->string('country_name', 80);
            $table->string('iso3', 3)->nullable();
            $table->smallInteger('numcode')->nullable();
            $table->integer('country_code');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new CountrySeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'countries');
    }
}
