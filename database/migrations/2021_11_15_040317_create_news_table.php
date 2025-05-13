<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_category_id')->index();

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
        Schema::dropIfExists('news');
    }
}
