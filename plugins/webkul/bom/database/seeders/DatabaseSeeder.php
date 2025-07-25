<?php

namespace Webkul\BOM\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BillOfMaterialSeeder::class,
        ]);
    }
}