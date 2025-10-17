<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locales = [
            ['code'=>'en','name'=>'English'],
            ['code'=>'fr','name'=>'French'],
            ['code'=>'es','name'=>'Spanish'],
        ];

        foreach ($locales as $l) {
            Locale::firstOrCreate(['code' => $l['code']], ['name' => $l['name']]);
        }
    }
}
