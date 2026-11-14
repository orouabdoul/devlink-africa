<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Flutter & Dart', '#0EA5E9'],
            ['React & React Native', '#06B6D4'],
            ['Laravel & PHP', '#EF4444'],
            ['JavaScript & TypeScript', '#EAB308'],
            ['Java & Spring Boot', '#F97316'],
            ['Python & Data', '#3B82F6'],
            ['IA & Machine Learning', '#8B5CF6'],
            ['UI/UX Design', '#EC4899'],
            ['DevOps & Cloud', '#14B8A6'],
            ['Entrepreneuriat Tech', '#16A34A'],
        ];

        foreach ($categories as [$name, $color]) {
            Category::updateOrCreate(
                ['slug' => Str::slug(str_replace('&', 'et', $name))],
                ['name' => $name, 'color' => $color],
            );
        }
    }
}
