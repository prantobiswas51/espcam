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
        $capturedAt = \Carbon\Carbon::parse($validated['captured_at'])->toImmutable();

        $filename = $capturedAt->format('Ymd_His_u') . '.jpg';
        $path = $file->storeAs("frames/{$cameraId}", $filename, ['disk' => 'public']);

        // Build the public URL (assuming you ran `php artisan storage:link`)
        $url = asset("storage/{$path}");

        // Save latest frame info into Redis
        $payload = [
            'camera_id'   => $cameraId,
            'captured_at' => $capturedAt->toIso8601String(),
            'url'         => $url,
        ];
        Redis::set("camera:{$cameraId}:latest", json_encode($payload));

        return response()->json([
            'ok'          => true,
            'camera_id'   => $cameraId,
            'captured_at' => $capturedAt->toIso8601String(),
            'path'        => $path,
            'url'         => $url,
            'size'        => $file->getSize(),
        ], 201);
    }
}
