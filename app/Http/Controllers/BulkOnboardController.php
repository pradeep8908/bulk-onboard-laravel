<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Jobs\ProcessOrganizationOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BulkOnboardController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'organizations' => 'required|array|max:1000',
            'organizations.*.name' => 'required|string',
            'organizations.*.domain' => 'required|string',
            'organizations.*.contact_email' => 'nullable|email',
        ]);

        $batchId = (string) Str::uuid();

        $payload = collect($request->organizations)
            ->map(fn ($org) => [
                'batch_id' => $batchId,
                'name' => $org['name'],
                'domain' => $org['domain'],
                'contact_email' => $org['contact_email'] ?? null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        // Bulk insert with chunking
        $payload->chunk(500)->each(function ($chunk) {
            Organization::upsert(
                $chunk->toArray(),
                ['domain'],
                ['name', 'contact_email', 'updated_at']
            );
        });

        // Dispatch jobs
        Organization::where('batch_id', $batchId)
            ->select('id')
            ->chunkById(200, function ($orgs) {
                foreach ($orgs as $org) {
                    ProcessOrganizationOnboarding::dispatch($org->id);
                }
            });

        Log::info('Bulk onboard request accepted', [
            'batch_id' => $batchId,
            'count' => $payload->count(),
        ]);

        return response()->json([
            'batch_id' => $batchId,
            'status' => 'accepted',
        ], 202);
    }

}
