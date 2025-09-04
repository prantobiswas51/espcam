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
        // If you require a Bearer token, check it here (or via middleware)
        $auth = $request->bearerToken();
        if ($auth !== 'HUUEEF76346G') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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

        $filename = $capturedAt->format('Ymd_His_u') . '.jpg';
        $path = $file->storeAs("frames/{$cameraId}", $filename, ['disk' => 'public']); // or default disk

        return response()->json([
            'ok'          => true,
            'camera_id'   => $cameraId,
            'captured_at' => $capturedAt->toIso8601String(),
            'path'        => $path,
            'size'        => $file->getSize(),
        ], 201);
    }
}
