<?php

namespace Database\Factories;

use App\Domain\Shared\PublicId;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Merchant> */
class MerchantFactory extends Factory
{
    protected $model = Merchant::class;

    public function definition(): array
    {
        return [
            'public_id' => PublicId::make('MER'),
            'name' => fake()->company(),
            'status' => 'active',
            'default_callback_url' => 'https://merchant.test/webhooks/payment',
        ];
    }
}
