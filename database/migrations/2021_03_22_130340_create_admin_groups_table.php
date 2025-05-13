<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'admin_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new AdminGroupSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'admin_groups');
    }
}
