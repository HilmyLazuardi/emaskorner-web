<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('unique_code', 50)->index('IDX_unique_code')->unique();
            $table->string('name')->comment('nama voucher');
            $table->enum('voucher_type', ['shipping', 'transaction'])->comment('shipping utk diskon ongkir / transaction utk diskon pembelanjaan');
            $table->enum('discount_type', ['amount', 'percentage']);
            $table->unsignedDecimal('discount_value', $precision = 8, $scale = 2);
            $table->unsignedInteger('discount_max_amount')->nullable()->comment('nilai nominal max diskon - bukan persen / jika null maka tidak dibatasi');
            $table->unsignedInteger('min_transaction')->comment('diskon baru bisa didapatkan jika nilai transaksi >= nilai ini');
            $table->text('description')->comment('syarat & ketentuan');
            $table->unsignedInteger('qty')->default(1)->comment('total kuota global');
            $table->unsignedInteger('qty_per_user')->default(1)->comment('total kuota per user');
            $table->dateTime('period_begin');
            $table->dateTime('period_end');
            $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('vouchers');
    }
}
