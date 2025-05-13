<?php

use Illuminate\Database\Seeder;

use App\Models\admin_group_member;
use App\Models\admin_group;
use App\Models\admin;

class AdminGroupMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadmin = admin::where('username', 'superuser')->first();
        $superadmin_group = admin_group::where('name', '*root')->first();

        $admin = admin::where('username', 'admin')->first();
        $admin_group = admin_group::where('name', 'Administrator')->first();

        $data = [
            [
                'admin_id' => $superadmin->id,
                'admin_group_id' => $superadmin_group->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'admin_id' => $admin->id,
                'admin_group_id' => $admin_group->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        admin_group_member::insert($data);
    }
}
