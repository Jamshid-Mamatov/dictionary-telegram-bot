<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DictionaryFactory extends Factory
{
    protected $model = \App\Models\Dictionary::class;

    public function definition()
    {
        return [
            'word' => $this->faker->word(),
            'definition' => $this->faker->sentence(),
            'language' => $this->faker->randomElement(['en', 'ru', 'uz']),
        ];
    }
}
