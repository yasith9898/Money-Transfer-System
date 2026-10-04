<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->currencyCode(),
            'name' => $this->faker->word() . ' Currency',
            'symbol' => $this->faker->randomElement(['$', '€', '£', '¥', '₹']),
            'is_active' => true,
            'decimal_places' => 2,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withDecimalPlaces(int $decimalPlaces): static
    {
        return $this->state(fn (array $attributes) => [
            'decimal_places' => $decimalPlaces,
        ]);
    }
}
