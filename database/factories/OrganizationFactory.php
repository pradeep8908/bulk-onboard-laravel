<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'batch_id' => Str::uuid(),
            'name' => $this->faker->company(),
            'domain' => $this->faker->unique()->domainName(),
            'contact_email' => $this->faker->companyEmail(),
            'status' => 'pending',
            'processed_at' => null,
            'failed_reason' => null,
        ];
    }
}
