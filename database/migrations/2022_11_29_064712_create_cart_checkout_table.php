<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartCheckoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_checkout', function (Blueprint $table) {
            $table->foreignId('buyer_id')->index('FK_buyer');
            $table->foreignId('product_item_variant_id')->index('FK_product_item_variant')->comment('product_item_variant.id');
            $table->integer('qty')->unsigned()->default(1);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_checkout');
    }
}
