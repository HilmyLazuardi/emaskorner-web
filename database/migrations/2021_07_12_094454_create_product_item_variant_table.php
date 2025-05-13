<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductItemVariantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_item_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_item_id')->index('FK_product_item');
            $table->string('sku_id', 100)->nullable()->comment('format("EK-YYYYMMDD-ID[substr(time(), 3)]"), sample: EK-20210804-1781');
            $table->string('name')->default('none');
            $table->integer('weight')->unsigned()->comment('(in grams)');
            $table->text('details')->nullable();
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
        Schema::dropIfExists('product_item_variant');
    }
}
