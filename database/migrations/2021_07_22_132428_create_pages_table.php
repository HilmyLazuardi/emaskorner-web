<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('slug', 100);
            $table->string('dir_path')->nullable();
            $table->text('thumbnail')->nullable();
            $table->text('summary')->nullable();
            $table->text('content')->nullable();
            $table->string('author')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->boolean('status')->default(0);

            $table->string('meta_title');
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_author')->nullable();

            $table->string('og_type')->nullable();
            $table->string('og_site_name')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_image')->nullable();
            $table->text('og_description')->nullable();
            
            $table->enum('twitter_card', ["summary", "summary_large_image", "app", "player"])->default('summary');
            $table->string('twitter_site')->nullable()->comment('@username for the website used in the card footer. Used with summary, summary_large_image, app, player cards.');
            $table->string('twitter_site_id')->nullable()->comment('Same as twitter:site, but the user’s Twitter ID. Either twitter:site or twitter:site:id is required. Used with summary, summary_large_image, player cards.');
            $table->string('twitter_creator')->nullable()->comment('@username for the content creator/author. Used with summary_large_image cards.');
            $table->string('twitter_creator_id')->nullable()->comment('Twitter user ID of content creator. Used with summary, summary_large_image cards.');
            
            $table->string('fb_app_id')->nullable();
            
            $table->text('header_script')->nullable()->comment('inserted before tag </head>');
            $table->text('body_script')->nullable()->comment('inserted after tag <body>');
            $table->text('footer_script')->nullable()->comment('inserted before tag </body>');

            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new PageSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'pages');
    }
}
