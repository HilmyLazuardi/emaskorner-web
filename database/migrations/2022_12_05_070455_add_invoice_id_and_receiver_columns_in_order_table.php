<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceIdAndReceiverColumnsInOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('id');
            $table->string('receiver_name', 255)->nullable()->after('shipment_remarks');
            $table->string('receiver_phone', 100)->nullable()->after('receiver_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->dropColumn(['invoice_id']);
            $table->dropColumn('receiver_name');
            $table->dropColumn('receiver_phone');
        });
    }
}
