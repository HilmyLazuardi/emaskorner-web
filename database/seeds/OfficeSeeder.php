<?php

use Illuminate\Database\Seeder;

use App\Models\office;

class OfficeSeeder extends Seeder
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
                'name' => 'Siorensys',
                'description' => 'Siorensys is a PHP Laravel Content Management System (CMS) using Bootstrap 4 Admin Dashboard Template that supports Multilingual, Multi-Office, User Access Management, Custom Navigation Menus, Page Builder, DB & File Log, System Logs with comparison changes, support AES-256 encryption, block IP, Error logs, some custom error pages (404, 419, 503), Social Media, and many else.',
                'ordinal' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        office::insert($data);
    }
}
