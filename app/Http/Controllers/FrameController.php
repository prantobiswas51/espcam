<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class FrameController extends Controller
{
    public function store(Request $request)
    {
        // Auth check
        $auth = $request->bearerToken();
        if ($auth !== 'HUUEEF76346G') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate input
        $validated = $request->validate([
            'camera_id'    => ['required', 'string'],
            'captured_at'  => ['required', 'date'],
            'frame'        => ['required', 'file', 'mimes:jpg,jpeg', 'max:10240'], // 10 MB
        ]);

        $file = $request->file('frame');
        if (!$file->isValid()) {
            return response()->json(['error' => 'Invalid file'], 422);
        }

        $cameraId   = $validated['camera_id'];
        $capturedAt = Carbon::parse($validated['captured_at'])->toImmutable();

        // Get raw binary
        $binary = file_get_contents($file->getRealPath());

        // Save raw image + metadata in Redis
        Redis::set("camera:{$cameraId}:latest:binary", $binary);
        Redis::set("camera:{$cameraId}:latest:meta", json_encode([
            'camera_id'   => $cameraId,
            'captured_at' => $capturedAt->toIso8601String(),
        ]));

        return response()->json([
            'ok'          => true,
            'camera_id'   => $cameraId,
            'captured_at' => $capturedAt->toIso8601String(),
            'size'        => $file->getSize(),
            'message'     => 'Frame stored in memory only (not on disk)',
        ], 201);
    }
}
