<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(env('PREFIX_TABLE') . 'offices', function (Blueprint $table) {
            $table->string('phone', 100)->nullable()->after('description');
            $table->string('fax', 100)->nullable()->after('phone');
            $table->string('email_office', 100)->nullable()->after('fax');
            $table->string('email_contact', 100)->nullable()->after('email_office');
            $table->string('wa_phone', 100)->nullable()->after('email_contact');
            $table->text('address')->nullable()->after('wa_phone');
            $table->text('gmaps')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(env('PREFIX_TABLE') . 'offices', function (Blueprint $table) {
            //
        });
    }
}
