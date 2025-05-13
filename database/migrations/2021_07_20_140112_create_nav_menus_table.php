<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNavMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'nav_menus', function (Blueprint $table) {
            $table->id();
            $table->enum('position', ['top', 'bottom', 'sidebar', 'footer'])->default('top');
            $table->string('name');
            $table->enum('link_type', ['none', 'internal', 'external']);
            $table->text('link_external')->nullable();
            $table->text('link_internal')->nullable();
            $table->enum('link_target', ['same window', 'new window'])->default('same window');
            $table->tinyInteger('level')->default(1);
            $table->foreignId('parent_id')->index()->nullable();
            $table->tinyInteger('ordinal')->default(1);
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new NavMenuSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'nav_menus');
    }
}
