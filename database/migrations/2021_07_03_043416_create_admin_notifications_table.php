<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(env('PREFIX_TABLE') . 'admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->index('FK_admins');
            $table->foreignId('module_id')->index('FK_modules')->nullable();
            $table->foreignId('target_id')->index('FK_target')->nullable();
            $table->string('subject', 250)->nullable();
            $table->text('content')->nullable();
            $table->boolean('clickable')->default(0);
            $table->boolean('read_status')->default(0)->comment('0:unread 1:read');
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
        Schema::dropIfExists(env('PREFIX_TABLE') . 'admin_notifications');
    }
}
