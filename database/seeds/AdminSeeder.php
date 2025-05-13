<?php

use Illuminate\Database\Seeder;

use App\Models\admin;

class AdminSeeder extends Seeder
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
                'firstname' => 'JcibbXxMKyFffS7U3VwZXI%3D',
                'lastname' => 'F97tTLmjLqZVE63VXNlcg%3D%3D',
                'username' => 'superuser',
                'email' => 'fkPuE5zpaH0ye3Kc3VwZXJ1c2VyQGRvbWFpbi5jb20%3D',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'password' => '$2y$10$acG4i6ta6lWj17vmH6GOAuNXR3zNi.ocas.3/2IyWf/W3F1hc/IfS',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        admin::insert($data);

        $data = new admin();
        $data->firstname = 'YsubUWTlnEwzFGoU3lzdGVt';
        $data->lastname = '9Hky56EpXkiWIrDQWRtaW4%3D';
        $data->username = 'admin';
        $data->email = 'gg329XDoNMWWm3uYWRtaW5AZG9tYWluLmNvbQ%3D%3D';
        $data->email_verified_at = date('Y-m-d H:i:s');
        $data->password = '$2y$10$Mmox80TUvTARaLXeuHsw2u1p11CBObCKZAWcKLR6mGkG08rpNIqiG';
        $data->save();
    }
}
