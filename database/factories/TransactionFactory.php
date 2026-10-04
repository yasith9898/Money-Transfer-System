<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\Currency;

class TransactionFactory extends Factory
{
    public function definition(): array
    {
        $fromAccount = Account::factory()->create();
        $toAccount = Account::factory()->create();
        $currency = Currency::factory()->create();

        return [
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'currency_id' => $currency->id,
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'office_commission' => $this->faker->randomFloat(2, 10, 100),
            'company_commission' => $this->faker->randomFloat(2, 10, 100),
            'type' => $this->faker->randomElement(['transfer', 'adjustment']),
            'status' => $this->faker->randomElement(['auto', 'manual', 'completed']),
            'notes' => $this->faker->sentence(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transfer',
            'status' => 'auto',
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'adjustment',
            'status' => 'manual',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function forAccounts(Account $fromAccount, Account $toAccount): static
    {
        return $this->state(fn (array $attributes) => [
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
        ]);
    }

    public function inCurrency($currencyId): static
    {
        return $this->state(fn (array $attributes) => [
            'currency_id' => $currencyId,
        ]);
    }
}
