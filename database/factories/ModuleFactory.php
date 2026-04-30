<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $sequence = 0;
        $sequence++;
        $name = $this->faker->sentence(3);
        $slug = 'module-'.$sequence;

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'icon' => 'fa-' . $this->faker->randomElement(['cube', 'cogs', 'chart', 'users', 'calendar', 'file']),
            'plan_id' => (($sequence - 1) % 50) + 1,
            'is_active' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the module should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the module should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
