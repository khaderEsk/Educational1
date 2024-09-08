<?php

namespace Database\Factories;

use App\Models\ProfileStudent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProfileStudent>
 */
class ProfileStudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ProfileStudent::class;

    public function definition(): array
    {
        return [

                'phone' => $this->faker->phoneNumber,
                'educational_level' => $this->faker->text(100),
                'user_id' => null,

        ];
    }
}
