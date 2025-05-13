<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScriptColumnsInConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(env('PREFIX_TABLE') . 'configs', function (Blueprint $table) {
            $table->text('header_script')->nullable()->comment('inserted before tag </head>')->after('twitter_creator_id');
            $table->text('body_script')->nullable()->comment('inserted after tag <body>')->after('header_script');
            $table->text('footer_script')->nullable()->comment('inserted before tag </body>')->after('body_script');
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
