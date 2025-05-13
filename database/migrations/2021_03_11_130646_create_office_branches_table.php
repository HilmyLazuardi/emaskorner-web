<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfficeBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'office_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->index('FK_offices');
            $table->string('name', 255);
            $table->text('address')->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('fax', 100)->nullable();
            $table->boolean('status')->default(1);
            $table->integer('ordinal');
            $table->timestamps();
            $table->softDeletes();
        });

        $seeder = new OfficeBranchSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'office_branches');
    }
}
