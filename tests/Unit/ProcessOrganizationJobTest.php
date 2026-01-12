<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Organization;
use App\Jobs\ProcessOrganizationOnboarding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProcessOrganizationJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function job_processes_pending_organization()
    {
        $org = Organization::create([
            'batch_id' => 'batch-123',
            'name' => 'Test Org',
            'domain' => 'test-org.com',
            'status' => 'pending',
        ]);

        (new ProcessOrganizationOnboarding($org->id))->handle();

        $org->refresh();

        $this->assertEquals('completed', $org->status);
        $this->assertNotNull($org->processed_at);
    }

    #[Test]
    public function job_is_idempotent_and_does_not_reprocess_completed_org()
    {
        $org = Organization::create([
            'batch_id' => 'batch-456',
            'name' => 'Completed Org',
            'domain' => 'completed.com',
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        (new ProcessOrganizationOnboarding($org->id))->handle();

        $this->assertEquals('completed', $org->fresh()->status);
    }

    #[Test]
    public function job_fails_gracefully_when_organization_not_found()
    {
        (new ProcessOrganizationOnboarding(999999))->handle();

        $this->assertTrue(true);
    }
}
