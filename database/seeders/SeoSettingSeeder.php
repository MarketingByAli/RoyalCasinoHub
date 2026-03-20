<?php

namespace Database\Seeders;

use App\Models\SeoSetting;
use Illuminate\Database\Seeder;

class SeoSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'RoyalCasinoHub',
            'meta_title_pattern' => '{Casino Name} Review {Year} — Bonuses, Games & Rating | {Site Name}',
            'meta_description_pattern' => 'Read our {Casino Name} review. Honest ratings, bonuses, games & more. Updated {Year}.',
            'meta_title_default' => 'RoyalCasinoHub — Trusted Online Casino Reviews & Ratings',
            'meta_description_default' => 'Discover trusted online casino reviews, ratings, and bonuses. Find the best casinos for your country.',
        ];

        foreach ($settings as $key => $value) {
            SeoSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
