<?php

use Illuminate\Database\Seeder;

use App\Models\phrase;

class PhraseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/phrases.json');
        $data = json_decode(file_get_contents($json), true);
        phrase::insert($data);
    }
}
