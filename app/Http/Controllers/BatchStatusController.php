<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;

class BatchStatusController extends Controller
{
    public function show(string $batchId): JsonResponse
    {
        $query = Organization::where('batch_id', $batchId);

        if (! $query->exists()) {
            return response()->json([
                'message' => 'Batch not found',
            ], 404);
        }

        return response()->json([
            'batch_id'   => $batchId,
            'total'      => $query->count(),
            'pending'    => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'completed'  => (clone $query)->where('status', 'completed')->count(),
            'failed'     => (clone $query)->where('status', 'failed')->count(),
        ]);
    }
}
