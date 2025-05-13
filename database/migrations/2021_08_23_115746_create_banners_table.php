<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->enum('position', ['home', 'blog'])->default('home');
            $table->string('name');
            $table->string('image');
            $table->string('image_thumb');
            $table->enum('link_type', ['none', 'internal', 'external']);
            $table->text('link_external')->nullable();
            $table->text('link_internal')->nullable();
            $table->enum('link_target', ['same window', 'new window'])->default('same window');
            $table->tinyInteger('ordinal')->default(1);
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('banners');
    }
}
