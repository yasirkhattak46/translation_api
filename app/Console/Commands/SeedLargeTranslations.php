<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use App\Models\Locale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedLargeTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:translations {count=100000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed translations in bulk for performance tests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int)$this->argument('count');
        $faker = Faker::create();
        $locales = Locale::all()->pluck('id')->toArray();

        if (empty($locales)) {
            $this->error('No locales found. Run LocaleSeeder first.');
            return 1;
        }

        $batchSize = 2000;
        $created = 0;

        $this->info("Seeding {$count} translations in batches of {$batchSize}...");

        while ($created < $count) {
            $rows = [];
            $toCreate = min($batchSize, $count - $created);
            for ($i = 0; $i < $toCreate; $i++) {
                $rows[] = [
                    'key' => 'app.' . $faker->unique()->bothify('label_##??'),
                    'locale_id' => $locales[array_rand($locales)],
                    'content' => $faker->sentence(8),
                    'meta' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('translations')->insert($rows);
            $created += $toCreate;
            $this->info("Inserted {$created}/{$count}");
            $faker->unique(true);
        }

        $this->info('Seeding complete.');
        return 0;
    }
}
