<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'forms', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['questionnaire','quiz'])->default('questionnaire');
            $table->string('title', 100);
            $table->string('slug', 100);
            $table->string('thumbnail')->nullable();
            $table->string('intro_title')->nullable();
            $table->text('intro_description')->nullable();
            $table->enum('intro_media', ['none','image','video'])->default('none');
            $table->string('intro_src')->nullable()->comment('NULL/image link/youtube link');
            $table->integer('form_point')->default(0);
            $table->boolean('must_complete')->default(true);
            $table->boolean('status')->default(false)->comment('0=draft | 1=published');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'forms');
    }
}
