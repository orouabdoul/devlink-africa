<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => fn () => Category::query()->inRandomOrder()->value('id')
                ?? Category::create(['name' => 'Laravel & PHP', 'slug' => 'laravel-et-php-'.Str::random(4)])->id,
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(2, true),
            'type' => fake()->randomElement(['question', 'discussion', 'resource']),
        ];
    }
}
