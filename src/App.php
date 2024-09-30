<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class App
{
    use Injection;
    use Singleton;

    public function __construct()
    {
        // if project root not defined, define it by taking the path of the first file from backtrace
        if (!defined("API_DIR")) {
            $bt = debug_backtrace();
            define("API_DIR", dirname(array_pop($bt)['file']) . DIRECTORY_SEPARATOR);
        }

        // register autoloader that matches the namespace\class to directory/class.php -- relative to index.php
        spl_autoload_register(function($class) {
            $array = explode('\\', $class);
            $name = array_pop($array);
            $path = API_DIR . strtolower(implode(DIRECTORY_SEPARATOR, $array)) . DIRECTORY_SEPARATOR . "$name.php";
            file_exists($path) && require_once $path;
        });
    }

    public function run(): void
    {
        $this->router->execute();
    }
}
