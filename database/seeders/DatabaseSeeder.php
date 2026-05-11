<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TalentsSeeder::class,
            MethodologyFormTemplateSeeder::class,
            CommercialSellersSeeder::class,
        ]);

        try {
            $this->call(ContractTemplateSeeder::class);
        } catch (\Throwable $e) {
            Log::warning('[DatabaseSeeder] ContractTemplateSeeder ignorado.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
