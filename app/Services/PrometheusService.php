<?php

namespace App\Services;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis as RedisStorage;

class PrometheusService
{
    private CollectorRegistry $registry;

    public function __construct()
    {
        if (! extension_loaded('redis')) {
            $this->registry = new CollectorRegistry(new InMemory());

            return;
        }

        RedisStorage::setDefaultOptions([
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => (int) env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD') ?: null,
            'database' => (int) env('PROMETHEUS_REDIS_DB', 15),
            'timeout' => 0.1,
            'read_timeout' => 10,
            'persistent_connections' => false,
        ]);

        $this->registry = new CollectorRegistry(
            new RedisStorage()
        );
    }

    public function registry(): CollectorRegistry
    {
        return $this->registry;
    }
}
