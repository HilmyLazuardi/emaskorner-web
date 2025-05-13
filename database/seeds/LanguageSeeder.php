<?php

use Illuminate\Database\Seeder;

use App\Models\language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/language_pack/ID.json');
        $json_value = file_get_contents($json);

        $data = [
            [
                'id' => 1,
                'country_id' => 100,
                'name' => 'Indonesia',
                'alias' => 'ID',
                'translations' => $json_value,
                'status' => 1,
                'ordinal' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'country_id' => 100,
                'name' => 'English',
                'alias' => 'EN',
                'translations' => null,
                'status' => 1,
                'ordinal' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        language::insert($data);
    }
}
