<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class Request
{
    use Injection;
    use Singleton;

    private array $data = [];

    public function __construct()
    {
        // get the values from get, post and php input (that's where Angular ajax calls are stored)
        try {
            $this->data = array_merge($_GET ?? [], $_POST ?? [], json_decode(file_get_contents('php://input'), true) ?? []);
        } catch (\Exception) {}
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function getDecodedId(): int|bool
    {
        return empty($this->data['id']) ? false : $this->code->decodeId($this->data['id']);
    }

    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function setAll($data): void
    {
        $this->data = $data;
    }

    public function has($key): bool
    {
        return isset($this->data[$key]);
    }

    public function all(): array
    {
        return $this->data;
    }
}
