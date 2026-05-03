<?php

use Illuminate\Support\Facades\Route;
use App\Services\CacheService;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-cache', function (CacheService $cache) {

    $key = 'intelecta:usuarios:total';
    $data = $cache->get($key, function () {
        return rand(1, 1000);
    }, 60);

    return [
        'key_usada' => $key,
        'valor' => $data,
        'timestamp' => now()
    ];
});
