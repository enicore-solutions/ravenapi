<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class Session
{
    use Singleton;

    public function get($key, $default = null)
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    public function has($key): bool
    {
        $this->start();
        return array_key_exists($key, $_SESSION);
    }

    public function set($key, $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function remove($key): void
    {
        $this->start();
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function start(): void
    {
        (session_status() == PHP_SESSION_ACTIVE) || session_start();
    }
}
