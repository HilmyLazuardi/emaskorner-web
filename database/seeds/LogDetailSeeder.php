<?php

use Illuminate\Database\Seeder;

use App\Models\log_detail;

class LogDetailSeeder extends Seeder
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
                'id' => 1,
                'action' => 'log in'
            ],
            [
                'id' => 2,
                'action' => 'log out'
            ],
            [
                'id' => 3,
                'action' => 'edit profile'
            ],
            [
                'id' => 4,
                'action' => 'change password'
            ],
            [
                'id' => 5,
                'action' => 'add new'
            ],
            [
                'id' => 6,
                'action' => 'view'
            ],
            [
                'id' => 7,
                'action' => 'update'
            ],
            [
                'id' => 8,
                'action' => 'delete'
            ],
            [
                'id' => 9,
                'action' => 'restore'
            ],
            [
                'id' => 10,
                'action' => 'reset password'
            ],
            [
                'id' => 11,
                'action' => 'export data'
            ],
            [
                'id' => 12,
                'action' => 'import data'
            ]
        ];

        log_detail::insert($data);
    }
}
