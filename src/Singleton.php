<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

trait Singleton {
    protected static $instance;

    public static function instance($options = null): static
    {
        return static::$instance ?? static::$instance = new static($options);
    }

    final public function __clone() {} // restrict cloning
    final public function __sleep() {} // restrict serializing
    final public function __wakeup() {} // restrict unserializing
}
