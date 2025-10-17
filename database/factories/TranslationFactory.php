<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'namespace.' . $this->faker->unique()->bothify('????_###') . '_' . uniqid(),
            'locale_id' => 1,
            'content' => $this->faker->sentence(6),
            'meta' => null,
        ];
    }
}
