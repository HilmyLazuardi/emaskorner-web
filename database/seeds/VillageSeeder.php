<?php

use Illuminate\Database\Seeder;

use App\Models\village;

class VillageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/kelurahan.json');
        $data = json_decode(file_get_contents($json), true);
        village::insert($data);
    }
}
