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
        $data = $request->validate([
            'camera_id'   => 'required|string|max:64',
            'captured_at' => 'nullable|date',
            'frame'       => 'required|image|mimes:jpg,jpeg,png|max:4096', // ~4MB
        ]);

        $cameraId = $data['camera_id'];
        $ts = isset($data['captured_at'])
            ? Carbon::parse($data['captured_at'])->format('Ymd_His_u')
            : now()->format('Ymd_His_u');

        // Store as: storage/app/public/frames/{camera_id}/{timestamp}.jpg
        $ext = $request->file('frame')->extension() ?: 'jpg';
        $path = $request->file('frame')->storeAs(
            "public/frames/{$cameraId}",
            "{$ts}.{$ext}"
        );

        // Save "latest" pointer in Redis (fast) so Blade can fetch quickly
        $publicUrl = Storage::url($path); // /storage/frames/{camera}/...
        $payload = json_encode([
            'url' => $publicUrl,
            'at'  => $ts,
            'ext' => $ext,
        ]);
        Redis::set("camera:{$cameraId}:latest", $payload);

        return response()->json(['ok' => true], 201);
    }
}
