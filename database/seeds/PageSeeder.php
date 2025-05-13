<?php

use Illuminate\Database\Seeder;

use App\Models\page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/page.json');
        $data = json_decode(file_get_contents($json), true);
        page::insert($data);
    }
}
