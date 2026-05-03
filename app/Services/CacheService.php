<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
  public function get(string $key, callable $callback, int $ttl = 300)
  {
    return Cache::remember($key, $ttl, $callback);
  }

  public function put(string $key, $value, int $ttl = 300)
  {
    Cache::put($key, $value, $ttl);
  }

  public function forget(string $key)
  {
    Cache::forget($key);
  }

  public function clear()
  {
    Cache::flush();
  }
}
