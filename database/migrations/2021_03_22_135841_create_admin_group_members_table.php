<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'admin_group_members', function (Blueprint $table) {
            $table->foreignId('admin_id')->index('FK_admins');
            $table->foreignId('admin_group_id')->index('FK_admin_groups');
            $table->timestamps();
        });

        $seeder = new AdminGroupMemberSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(env('PREFIX_TABLE') . 'admin_group_members');
    }
}
