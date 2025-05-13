<?php

use Illuminate\Database\Seeder;

use App\Models\admin_group_branch;
use App\Models\admin_group;
use App\Models\office_branch;

class AdminGroupBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin_group = admin_group::where('name', 'Administrator')->first();
        $office_branch = office_branch::first();

        $data = [
            [
                'admin_group_id' => $admin_group->id,
                'office_branch_id' => $office_branch->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        admin_group_branch::insert($data);
    }
}
