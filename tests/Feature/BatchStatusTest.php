<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BatchStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_batch_status()
    {
        Organization::factory()->count(3)->create([
            'batch_id' => 'batch-123',
            'status' => 'completed',
        ]);

        $this->getJson('/api/batch/batch-123')
            ->assertStatus(200)
            ->assertJson([
                'batch_id' => 'batch-123',
                'completed' => 3,
            ]);
    }
}
