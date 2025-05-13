<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'configs', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->string('app_version', 10);
            $table->string('app_url_site')->comment('Base URL this application');
            $table->string('app_url_main')->nullable()->comment('Main URL, if this application used for manage microsite');
            $table->string('app_favicon');
            $table->string('app_logo');
            $table->string('app_copyright_year', 4);
            $table->string('app_info');
            $table->enum('app_skin', ['default','festive_yellow', 'feminine_purple', 'racing_red', 'calm_blue', 'simple_black', 'manly_maroon', 'green_nature'])->default('calm_blue');
            $table->string('powered_by')->nullable();
            $table->string('powered_by_url')->nullable();

            $table->string('meta_title');
            $table->text('meta_description');
            $table->text('meta_keywords');
            $table->string('meta_author');

            $table->string('og_type')->nullable();
            $table->string('og_site_name')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_image')->nullable();
            $table->text('og_description')->nullable();

            $table->string('fb_app_id')->nullable();

            $table->enum('twitter_card', ["summary", "summary_large_image", "app", "player"])->default('summary');
            $table->string('twitter_site')->nullable()->comment('@username for the website used in the card footer. Used with summary, summary_large_image, app, player cards.');
            $table->string('twitter_site_id')->nullable()->comment('Same as twitter:site, but the user’s Twitter ID. Either twitter:site or twitter:site:id is required. Used with summary, summary_large_image, player cards.');
            $table->string('twitter_creator')->nullable()->comment('@username for the content creator/author. Used with summary_large_image cards.');
            $table->string('twitter_creator_id')->nullable()->comment('Twitter user ID of content creator. Used with summary, summary_large_image cards.');

            $table->timestamps();
        });

        $seeder = new ConfigSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'configs');
    }
}
