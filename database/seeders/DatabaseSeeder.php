<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        // Les données de démonstration ne sont créées qu'une fois (base vide).
        if (\App\Models\Post::count() === 0) {
            $this->call(DemoSeeder::class);
        }
    }
}
