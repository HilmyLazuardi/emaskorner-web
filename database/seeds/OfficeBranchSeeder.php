<?php

use Illuminate\Database\Seeder;

use App\Models\office_branch;
use App\Models\office;

class OfficeBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $office = office::first();

        $data = [
            [
                'id' => random_int(1, 100),
                'office_id' => $office->id,
                'name' => 'Headquarter',
                'ordinal' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        office_branch::insert($data);
    }
}
