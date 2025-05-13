<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index('FK_order');
            $table->foreignId('product_id')->index('FK_product_item_variant')->comment('product_item_variant.id');
            $table->integer('qty')->unsigned()->default(1);
            $table->integer('price_per_item')->unsigned();
            $table->text('remarks')->nullable();
            $table->integer('price_subtotal')->unsigned()->comment('(qty x price_per_item)');
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
        Schema::dropIfExists('order_details');
    }
}
