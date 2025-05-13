<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->index('FK_category');
            $table->foreignId('seller_id')->index('FK_seller');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('summary')->nullable();
            $table->string('image');
            $table->text('details')->nullable();
            $table->integer('qty')->unsigned();
            $table->integer('price')->unsigned();
            $table->datetime('campaign_start')->nullable();
            $table->datetime('campaign_end')->nullable();
            $table->boolean('featured')->default(0);
            $table->boolean('approval_status')->default(0);
            $table->boolean('published_status')->default(0);
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
        Schema::dropIfExists('product_item');
    }
}
