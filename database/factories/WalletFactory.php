<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // Automatically create a user if not provided
            'balance' => $this->faker->randomFloat(2, 0, 1000), // Random balance between 0 and 1000
        ];
    }
}
