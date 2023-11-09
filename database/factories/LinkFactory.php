<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => fake()->unique()->lexify('?????????'),
            'title' => fake()->words(3, true),
            'target_url' => 'https://example.org/'.fake()->slug(),
            'is_active' => true,
            'expires_at' => null,
        ];
    }
}
