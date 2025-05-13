<?php

use Illuminate\Database\Seeder;

use App\Models\nav_menu;

class NavMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/nav_menus.json');
        $data = json_decode(file_get_contents($json), true);
        nav_menu::insert($data);
    }
}
