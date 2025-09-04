<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/latest-frame/{cameraId}', function (string $cameraId) {
    $binary = Redis::get("camera:{$cameraId}:latest:binary");

    if (!$binary) {
        return response('Not found', 404);
    }

    return response($binary, 200)->header('Content-Type', 'image/jpeg');
});
