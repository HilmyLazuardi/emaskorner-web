<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminGroupBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'admin_group_branches', function (Blueprint $table) {
            $table->foreignId('admin_group_id')->index('FK_admin_groups');
            $table->foreignId('office_branch_id')->index('FK_office_branches');
            $table->timestamps();
        });

        $seeder = new AdminGroupBranchSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'admin_group_branches');
    }
}
