<?php

use Illuminate\Database\Seeder;

use App\Models\config;

class ConfigSeeder extends Seeder
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
                'app_name' => 'Siorensys',
                'app_version' => '1.0',
                'app_url_site' => 'http://localhost/siorensys/public/',
                'app_favicon' => 'favicon.ico',
                'app_logo' => 'images/logo-square.png',
                'app_copyright_year' => date('Y'),
                'app_info' => 'Content Management System for Website Siorensys',
                'powered_by' => 'KINIDI Tech',
                'powered_by_url' => 'https://kiniditech.com',

                'meta_title' => 'Siorensys - a PHP Laravel CMS',
                'meta_description' => 'Siorensys is a PHP Laravel CMS that support Multilingual & User Access Management',
                'meta_keywords' => 'kiniditech,kinidi tech,kinidi,laravel,siorensys,php,skeleton,cms,content management system,dashboard,admin,website',
                'meta_author' => 'KINIDI Tech',

                'og_type' => 'website',
                'og_site_name' => 'Siorensys',
                'og_title' => 'Siorensys - a PHP Laravel CMS',
                'og_image' => 'images/logo-square.png',
                'og_description' => 'Siorensys is a PHP Laravel CMS that support Multilingual & User Access Management',

                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        config::insert($data);
    }
}
