<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantColumnToProductItemVariantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_item_variant', function (Blueprint $table) {
            $table->string('variant_1', 100)->nullable()->after('product_item_id');
            $table->string('variant_2', 100)->nullable()->after('variant_1');
            $table->string('variant_image')->nullable()->after('variant_2');
            $table->string('slug', 255)->after('variant_image');

            $table->integer('qty')->unsigned()->nullable()->after('weight');
            $table->integer('qty_booked')->unsigned()->nullable()->after('qty');
            $table->integer('qty_sold')->unsigned()->nullable()->after('qty_booked');
            $table->integer('price')->unsigned()->after('qty_sold');

            $table->boolean('is_default')->default(0)->after('details');
            $table->boolean('status')->default(1)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_item_variant', function (Blueprint $table) {
            $table->dropColumn('variant_1');
            $table->dropColumn('variant_2');
            $table->dropColumn('variant_image');
            $table->dropColumn('slug');

            $table->dropColumn('qty');
            $table->dropColumn('qty_booked');
            $table->dropColumn('qty_sold');
            $table->dropColumn('price');

            $table->dropColumn('is_default');
            $table->dropColumn('status');
        });
    }
}
