<?php

namespace App\Core\Middleware;

use Redis;

class RateLimitMiddleware
{
    private $redis;
    private $maxRequests = 60;
    private $perMinutes = 1;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect(
            $_ENV['REDIS_HOST'] ?? 'localhost',
            $_ENV['REDIS_PORT'] ?? 6379
        );
    }

    public function handle($request, $next)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:$ip";
        
        $current = $this->redis->get($key);
        if (!$current) {
            $this->redis->setex($key, 60 * $this->perMinutes, 1);
        } else if ($current >= $this->maxRequests) {
            header('HTTP/1.1 429 Too Many Requests');
            echo json_encode([
                'error' => 'Rate limit exceeded'
            ]);
            exit();
        } else {
            $this->redis->incr($key);
        }
        
        return $next($request);
    }
} 