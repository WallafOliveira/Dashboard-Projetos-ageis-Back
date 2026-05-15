<?php

namespace Database\Seeders;

use App\Models\Produto;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Truncar produtos antes de chamar o seeder para evitar duplicatas
        Produto::truncate();
        
        // Chamar os seeders
        $this->call(ProdutoSeeder::class);
        $this->call(OperacionalSeeder::class);
    }
}
