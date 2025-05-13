<?php

use Illuminate\Database\Seeder;

use App\Models\admin_group;

class AdminGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => random_int(1, 100),
                'name' => '*root',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        admin_group::insert($data);

        $data = new admin_group();
        $data->name = 'Administrator';
        $data->save();
    }
}
