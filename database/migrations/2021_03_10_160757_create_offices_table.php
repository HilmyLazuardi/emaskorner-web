<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'offices', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('logo', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->integer('ordinal');
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new OfficeSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'offices');
    }
}
