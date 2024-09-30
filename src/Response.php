<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

use JetBrains\PhpStorm\NoReturn;

class Response
{
    use Singleton;

    #[NoReturn]
    public function success(array $data = []): void
    {
        ob_get_contents() && @ob_end_clean();
        exit(json_encode(["success" => true, "data" => $data]));
    }

    #[NoReturn]
    public function error(string $message = "", array $data = []): void
    {
        ob_get_contents() && @ob_end_clean();
        exit(json_encode(["success" => false, "message" => $message, "data" => $data]));
    }

    #[NoReturn]
    public function terminate(int $code = 404, string $message = ""): void
    {
        ob_get_contents() && @ob_end_clean();

        if ($message) {
            header("HTTP/1.1 $code " . $message);
        } else {
            http_response_code($code);
        }

        exit();
    }
}
