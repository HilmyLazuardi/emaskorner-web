<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->index('FK_countries');
            $table->string('name', 20);
            $table->string('alias', 10);
            $table->text('translations')->nullable()->comment('JSON formatted');
            $table->boolean('status')->default(1);
            $table->integer('ordinal');
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new LanguageSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'languages');
    }
}
