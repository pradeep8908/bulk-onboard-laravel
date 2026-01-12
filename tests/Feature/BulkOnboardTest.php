<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessOrganizationOnboarding;
use PHPUnit\Framework\Attributes\Test;

class BulkOnboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_accepts_bulk_onboard_request_and_returns_batch_id(): void
    {
        Queue::fake();

        $payload = [
            'organizations' => [
                [
                    'name' => 'Acme Corp',
                    'domain' => 'acme.com',
                    'contact_email' => 'admin@acme.com',
                ],
                [
                    'name' => 'Beta Corp',
                    'domain' => 'beta.com',
                    'contact_email' => 'contact@beta.com',
                ],
            ],
        ];

        $response = $this->postJson('/api/bulk-onboard', $payload);

        $response
            ->assertStatus(202)
            ->assertJsonStructure([
                'batch_id',
                'status',
            ]);

        $this->assertDatabaseCount('organizations', 2);

        $this->assertDatabaseHas('organizations', [
            'domain' => 'acme.com',
            'status' => 'pending',
        ]);

        Queue::assertPushed(ProcessOrganizationOnboarding::class, 2);
    }

    #[Test]
    public function it_validates_payload_structure(): void
    {
        $response = $this->postJson('/api/bulk-onboard', [
            'organizations' => [
                [
                    'domain' => 'invalid.com',
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'organizations.0.name',
            ]);
    }

    #[Test]
    public function it_does_not_insert_duplicate_domains(): void
    {
        Organization::create([
            'batch_id' => 'existing-batch',
            'name' => 'Existing Org',
            'domain' => 'duplicate.com',
            'status' => 'completed',
        ]);

        $payload = [
            'organizations' => [
                [
                    'name' => 'Duplicate Org',
                    'domain' => 'duplicate.com',
                ],
            ],
        ];

        $this->postJson('/api/bulk-onboard', $payload)
            ->assertStatus(202);

        // Due to upsert + unique constraint
        $this->assertDatabaseCount('organizations', 1);
    }
}
