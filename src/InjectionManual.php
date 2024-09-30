<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

trait InjectionManual
{
    protected $auth;
    protected $code;
    protected $db;
    protected $request;
    protected $response;
    protected $router;
    protected $session;

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
     * Set all the dependencies to local class variables. This can be used if the class uses the __get() method on its own and overrides
     * the method in this trait.
     */
    public function setDependencies(): void
    {
        foreach ($this->classes as $key => $class) {
            $this->$key = $class::instance();
        }
    }
}
