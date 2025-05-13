<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnRecaptchaInConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(env('PREFIX_TABLE') . 'configs', function (Blueprint $table) {
            $table->text('recaptcha_site_key_admin')->nullable()->after('twitter_creator_id');
            $table->text('recaptcha_secret_key_admin')->nullable()->after('recaptcha_site_key_admin');
            $table->text('recaptcha_site_key_public')->nullable()->after('recaptcha_secret_key_admin');
            $table->text('recaptcha_secret_key_public')->nullable()->after('recaptcha_site_key_public');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(env('PREFIX_TABLE') . 'configs', function (Blueprint $table) {
            //
        });
    }
}
