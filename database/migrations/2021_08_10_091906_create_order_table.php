<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 100)->nullable()->comment('booking_id/invoice_id | format("TLK-YYYYMMDDHHIISS-ID[substr(time(), 3)"), sample: TLK-20210804183021-1982');
            $table->foreignId('seller_id')->index();

            $table->foreignId('buyer_id')->index();
            $table->foreignId('shipment_province_code')->index();
            $table->foreignId('shipment_district_code')->index();
            $table->foreignId('shipment_sub_district_code')->index();
            $table->foreignId('shipment_village_code')->index();
            $table->integer('shipment_postal_code');
            $table->text('shipment_address_details')->nullable();

            $table->foreignId('shipper_id')->index();
            $table->string('shipper_name', 100)->comment('keterangan pakai kurir apa, contoh: JNE, AnterAja');
            $table->string('shipper_service_type', 100)->comment('keterangan pakai jenis layanan kurir apa, contoh: REG/YES/Instant');
            $table->unsignedInteger('shipment_total_weight')->comment('dalam gram');
            $table->string('shipping_number')->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->date('estimate_arrived_at')->nullable();
            $table->unsignedInteger('price_shipping');
            $table->boolean('use_insurance_shipping')->default(false);
            $table->unsignedInteger('insurance_shipping_fee')->nullable();

            $table->unsignedInteger('price_subtotal')->comment('sum of price_subtotal in order_details table');
            $table->unsignedInteger('price_discount')->default(0);
            $table->integer('price_total')->unsigned()->comment('(price_shipping + insurance_shipping_fee + price_subtotal - price_discount)');

            $table->text('order_remarks')->nullable();

            $table->foreignId('payment_result_id')->index()->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('payment_channel', 100)->nullable();
            $table->text('payment_remarks')->nullable()->comment('untuk mencatat notes dari payment gateway');
            $table->dateTime('paid_at')->nullable();
            $table->tinyInteger('payment_status')->default(0)->comment('0:unpaid | 1:paid');

            $table->foreignId('progress_status')->index()->default(1)->comment('1=waiting for payment | 2=paid | 3=shipped | 4=canceled');
            
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
        Schema::dropIfExists('order');
    }
}
