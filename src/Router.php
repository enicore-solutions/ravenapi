<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class Router
{
    use Injection;
    use Singleton;

    public function execute(): void
    {
        // if uploading a file the post data comes in $_POST--let's add $_FILES to it and load it to the request
        if (!empty($_FILES) && !empty($_POST['data'])) {
            $post = json_decode($_POST['data'], true);
            $post['files'] = $_FILES;
            $this->request->setAll($post);
        }

        // check if controller and action are set
        if (empty($controller = $this->request->get("controller"))) {
            $this->response->terminate(405, "Missing route [299482]");
        }

        if (empty($method = $this->request->get("action"))) {
            $this->response->terminate(405, "Missing route [299483]");
        }

        // format controller and method
        $controller = "Controllers\\" . ucfirst($controller) . "Controller";
        $method = str_replace("-", "", $method) . "Action";

        if (($attributes = $this->getMethodAttributes($controller, $method)) === false) {
            $this->response->terminate(405, "No route [229338]");
        }

        // if #[NoAuth] not specified, check if user logged in and if the csrf token is correct
        if (!in_array("NoAuth", $attributes)) {
            if (!($userData = $this->auth->getUserData())) {
                $this->response->terminate(401);
            }

            if (!($this->verifyCsrfToken($userData))) {
                $this->response->terminate(403);
            }
        }

        // execute the method in the controller, make sure an array is passed to success
        $result = (new $controller())->$method();
        $this->response->success(is_array($result) ? $result : []);
    }

    /**
     * Retrieves method attributes, currently available: #[NoAuth], later add support for others, for example: #[Admin]
     * This function sends a 405 response if the controller or method doesn't exist.
     * @param string $controller
     * @param string $method
     * @return array|bool
     */
    private function getMethodAttributes(string $controller, string $method): array|bool
    {
        $attributes = [];
        try {
            $reflection = new \ReflectionMethod($controller, $method);
            foreach ($reflection->getAttributes() as $attribute) {
                $attributes[] = substr(strrchr($attribute->getName(), "\\"), 1);
            }
        } catch (\Exception) {
            return false;
        }

        return $attributes;
    }

    /**
     * Verifies CSRF token.
     * @param array $userData
     * @return bool
     */
    private function verifyCsrfToken(array $userData): bool
    {
        foreach ([$_SERVER, apache_request_headers()] as $array) {
            foreach (["Authorization", "authorization", "HTTP_AUTHORIZATION"] as $key) {
                if (isset($array[$key])) {
                    return preg_match('/Bearer\s(\S+)/', trim($array[$key]), $matches) && $userData['token'] == $matches[1];
                }
            }
        }

        return false;
    }
}
