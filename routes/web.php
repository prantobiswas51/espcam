<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/latest-frame/{cameraId}', function (string $cameraId) {
    $json = Redis::get("camera:{$cameraId}:latest");
    if (!$json) {
        return response()->json(['url' => null], 404);
    }
    return response($json, 200)->header('Content-Type', 'application/json');
});