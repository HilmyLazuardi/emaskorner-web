<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'faq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->index()->nullable();
            $table->text('text_1')->nullable();
            $table->text('text_2')->nullable();
            $table->tinyInteger('level')->default(1);
            $table->boolean('status')->default(1);
            $table->tinyInteger('ordinal')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new FaqSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'faq');
    }
}
