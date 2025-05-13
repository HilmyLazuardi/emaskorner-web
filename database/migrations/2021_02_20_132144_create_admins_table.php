<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'admins', function (Blueprint $table) {
            $table->id();
            $table->text('firstname');
            $table->text('lastname');
            $table->string('username', 100)->unique();
            $table->string('email', 255)->unique();
            $table->datetime('email_verified_at')->nullable();
            $table->text('phone')->nullable();
            $table->string('password');
            $table->boolean('status')->default(1);
            $table->text('remarks')->nullable();
            $table->boolean('force_logout')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new AdminSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'admins');
    }
}
