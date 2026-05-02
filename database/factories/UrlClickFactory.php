<?php

namespace Database\Factories;

use App\Models\Url;
use App\Models\UrlClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UrlClick>
 */
class UrlClickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url_id' => Url::factory(),
            'clicked_at' => fake()->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'ip_address' => fake()->optional()->ipv4(),
            'user_agent' => fake()->optional()->userAgent(),
            'referrer' => fake()->optional()->url(),
            'via_qr' => false,
        ];
    }

    /**
     * Indicate the click came from a QR code scan.
     */
    public function viaQr(): static
    {
        return $this->state(fn () => [
            'via_qr' => true,
            'clicked_at' => now(),
        ]);
    }
}
