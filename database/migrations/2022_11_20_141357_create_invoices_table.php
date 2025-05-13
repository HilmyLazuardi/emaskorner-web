<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 100)->index('IDX_invoice_no')->unique();
            $table->foreignId('buyer_id')->index('FK_buyer');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('shipping_fee');
            $table->unsignedInteger('shipping_insurance_fee')->default(0);
            $table->unsignedInteger('discount_amount')->default(0)->comment('jika pakai voucher, simpan nominal diskon');
            $table->unsignedInteger('total_amount');

            $table->foreignId('voucher_id')->index('FK_voucher')->nullable();
            $table->string('voucher_code', 50)->nullable();
            $table->string('voucher_type', 50)->nullable();
            
            $table->string('payment_url')->nullable();
            $table->foreignId('payment_result_id')->index()->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('payment_channel', 100)->nullable();
            $table->text('payment_remarks')->nullable()->comment('untuk mencatat notes dari payment gateway');
            $table->dateTime('paid_at')->nullable();
            $table->boolean('payment_status')->default(0)->comment('0:unpaid | 1:paid');
            $table->dateTime('expired_at')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
