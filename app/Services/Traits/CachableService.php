<?php

namespace App\Services\Traits;

use Illuminate\Support\Facades\Cache;

trait CachableService
{
    protected function getCacheConfig(): array
    {
        return [
            'ttl' => $this->getCacheTtl(),
            'tags' => $this->getCacheTags(),
            'prefix' => $this->getCachePrefix(),
        ];
    }

    protected function getCacheTtl(): int
    {
        return property_exists($this, 'cacheTtl') ? $this->cacheTtl : 3600;
    }

    protected function getCacheTags(): array
    {
        return property_exists($this, 'cacheTags') ? $this->cacheTags : [$this->getServiceName()];
    }

    protected function getCachePrefix(): string
    {
        return property_exists($this, 'cachePrefix') ? $this->cachePrefix : 'bip';
    }

    protected function getServiceName(): string
    {
        $className = class_basename(static::class);
        return strtolower(str_replace('Service', '', $className));
    }

    protected function generateCacheKey(string $method, array $params = []): string
    {
        $config = $this->getCacheConfig();
        $serviceName = $this->getServiceName();
        
        $keyParts = [
            $config['prefix'],
            $serviceName,
            $method
        ];

        if (!empty($params)) {
            $keyParts[] = md5(serialize($params));
        }

        return implode(':', array_filter($keyParts));
    }

    protected function cacheGet(string $method, array $params = [], $callback = null, ?int $customTtl = null): mixed
    {
        if (!$this->shouldCache()) {
            return $callback ? $callback() : null;
        }

        $config = $this->getCacheConfig();
        $cacheKey = $this->generateCacheKey($method, $params);
        $ttl = $customTtl ?? $config['ttl'];
        
        return Cache::tags($config['tags'])->remember($cacheKey, $ttl, $callback);
    }

    protected function cacheForget(string $method, array $params = []): void
    {
        $config = $this->getCacheConfig();
        $cacheKey = $this->generateCacheKey($method, $params);
        Cache::tags($config['tags'])->forget($cacheKey);
    }

    protected function cacheInvalidate(array $additionalTags = []): void
    {
        $config = $this->getCacheConfig();
        $tags = array_merge($config['tags'], $additionalTags);
        
        Cache::tags($tags)->flush();
    }

    protected function cacheUpdate(string $method, array $params = [], $callback = null, ?int $customTtl = null): mixed
    {
        $this->cacheForget($method, $params);
        return $this->cacheGet($method, $params, $callback, $customTtl);
    }

    protected function cachePut(string $method, $data, array $params = [], ?int $customTtl = null): void
    {
        if (!$this->shouldCache()) {
            return;
        }

        $config = $this->getCacheConfig();
        $cacheKey = $this->generateCacheKey($method, $params);
        $ttl = $customTtl ?? $config['ttl'];
        
        Cache::tags($config['tags'])->put($cacheKey, $data, $ttl);
    }

    protected function cacheExists(string $method, array $params = []): bool
    {
        if (!$this->shouldCache()) {
            return false;
        }

        $config = $this->getCacheConfig();
        $cacheKey = $this->generateCacheKey($method, $params);

        // IMPORTANT : Utiliser tags() comme dans cachePut()
        return Cache::tags($config['tags'])->has($cacheKey);
    }

    protected function shouldCache(): bool
    {
        return config('cache.default') !== 'null' && 
               !app()->environment('testing') &&
               (!property_exists($this, 'cacheEnabled') || $this->cacheEnabled === true);
    }

    protected function warmCache(string $method, array $params = [], $callback = null): void
    {
        if ($callback) {
            $this->cacheGet($method, $params, $callback);
        }
    }

    protected function refreshCache(string $method, array $params = [], $callback = null, ?int $customTtl = null): mixed
    {
        // 1. Invalider le cache spécifique
        $this->cacheForget($method, $params);
        
        // 2. Reconstruire avec nouvelles données
        if ($callback) {
            return $this->cacheGet($method, $params, $callback, $customTtl);
        }
        
        return null;
    }

    protected function refreshAllCache(array $refreshMethods = []): void
    {
        // 1. Invalider tout le cache du service
        $this->cacheInvalidate();
        
        // 2. Reconstruire les caches principaux
        foreach ($refreshMethods as $methodName => $methodConfig) {
            try {
                if (is_callable($methodConfig['callback'])) {
                    $this->cacheGet(
                        $methodConfig['method'] ?? $methodName,
                        $methodConfig['params'] ?? [],
                        $methodConfig['callback'],
                        $methodConfig['ttl'] ?? null
                    );
                }
            } catch (Exception $e) {
                \Log::warning("Failed to refresh cache for method {$methodName}: " . $e->getMessage());
            }
        }
    }

    public function clearServiceCache(): void
    {
        $this->cacheInvalidate();
    }

    public function getCacheInfo(): array
    {
        $config = $this->getCacheConfig();
        return [
            'service_name' => $this->getServiceName(),
            'ttl' => $config['ttl'],
            'tags' => $config['tags'],
            'prefix' => $config['prefix'],
            'cache_enabled' => $this->shouldCache()
        ];
    }

    protected function cacheRemember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        if (!$this->shouldCache()) {
            return $callback();
        }

        $config = $this->getCacheConfig();
        $allTags = array_merge($config['tags'], $tags);
        
        return Cache::tags($allTags)->remember($key, $ttl, $callback);
    }
}