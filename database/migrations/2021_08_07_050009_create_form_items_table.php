<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'form_items', function (Blueprint $table) {
            $table->foreignId('form_id')->index();
            $table->unsignedTinyInteger('ordinal');
            $table->text('question_text');
            $table->text('response_wording')->nullable()->comment('(optional) used in summary answers instead of questions');
            $table->string('question_media', 20)->nullable()->comment('NULL/image/video');
            $table->string('question_src')->nullable()->comment('NULL/image link/youtube link');
            $table->enum('question_type', ['multiple_choice', 'checkboxes', 'drop-down', 'linear_scale']);
            
            $table->enum('option_type', ['text', 'image']);
            $table->boolean('option_other')->default(false);
            $table->text('options_text')->nullable()->comment('sample: ["A","B","C","D","other"]');
            $table->text('options_media')->nullable()->comment('sample: ["A.jpg","B.jpg","C.jpg","D.jpg"]');
            
            $table->integer('point_per_item')->default(0)->comment('if QUIZ, you can give point per item if user answer correctly');
            $table->tinyInteger('option_answer_index')->nullable()->comment('if QUIZ, filled by index of option as answer');
            $table->text('option_answer_text')->nullable()->comment('if QUIZ, filled by text of option as answer');
            $table->text('option_answer_media')->nullable()->comment('if QUIZ, filled by media of option as answer');
            
            $table->boolean('is_required')->default(true);
            $table->boolean('checkpoint_status')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'form_items');
    }
}
