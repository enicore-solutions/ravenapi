<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

trait Injection
{
    private array $classes = [
        "auth" => "RavenApi\\Auth",
        "code" => "RavenApi\\Code",
        "db" => "RavenApi\\Database",
        "request" => "RavenApi\\Request",
        "response" => "RavenApi\\Response",
        "router" => "RavenApi\\Router",
        "session" => "RavenApi\\Session",
    ];

    /**
     * Catch the calls to $this->something and return the singleton class.
     * @param string $name
     * @return object|null
     */
    public function __get(string $name): ?object
    {
        return array_key_exists($name, $this->classes) ? $this->classes[$name]::instance() : null;
    }
}
