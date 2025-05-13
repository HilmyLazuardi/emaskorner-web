<?php

use Illuminate\Database\Seeder;

use App\Models\social_media;

class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = public_path('admin/json/social_medias.json');
        $data = json_decode(file_get_contents($json), true);
        social_media::insert($data);
    }
}
