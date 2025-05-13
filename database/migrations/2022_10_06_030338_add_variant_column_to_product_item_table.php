<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantColumnToProductItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_item', function (Blueprint $table) {
            $table->boolean('global_stock')->default(1)->after('details');

            $table->string('variant_1', 100)->nullable()->after('qty_sold');
            $table->text('variant_1_list')->nullable()->after('variant_1');

            $table->string('variant_2', 100)->nullable()->after('variant_1_list');
            $table->text('variant_2_list')->nullable()->after('variant_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_item', function (Blueprint $table) {
            $table->dropColumn('global_stock');

            $table->dropColumn('variant_1');
            $table->dropColumn('variant_1_list');

            $table->dropColumn('variant_2');
            $table->dropColumn('variant_2_list');
        });
    }
}
