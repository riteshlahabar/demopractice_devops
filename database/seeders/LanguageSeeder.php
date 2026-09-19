<?php

namespace Database\Seeders;

use App\Models\Communication\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => true, 'is_active' => true, 'sort_order' => 1],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'is_default' => false, 'is_active' => true, 'sort_order' => 2],
            ['code' => 'mr', 'name' => 'Marathi', 'native_name' => 'मराठी', 'is_default' => false, 'is_active' => true, 'sort_order' => 3],
            ['code' => 'gu', 'name' => 'Gujarati', 'native_name' => 'ગુજરાતી', 'is_default' => false, 'is_active' => true, 'sort_order' => 4],
            ['code' => 'kn', 'name' => 'Kannada', 'native_name' => 'ಕನ್ನಡ', 'is_default' => false, 'is_active' => true, 'sort_order' => 5],
            ['code' => 'te', 'name' => 'Telugu', 'native_name' => 'తెలుగు', 'is_default' => false, 'is_active' => true, 'sort_order' => 6],
        ];

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(['code' => $language['code']], $language);
        }

        Language::query()
            ->whereNotIn('code', collect($languages)->pluck('code')->all())
            ->where('is_default', false)
            ->update(['is_active' => false]);
    }
}
