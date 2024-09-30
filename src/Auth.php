<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class Auth
{
    use Injection;
    use Singleton;

    public function isLoggedIn(): bool
    {
        return !empty($this->session->get("userData"));
    }

    public function getUserData()
    {
        return $this->session->get("userData");
    }

    public function getUserId()
    {
        return ($userData = $this->getUserData()) ? $userData['userId'] : false;
    }

    public function get(string $key)
    {
        $userData = $this->getUserData();
        return $userData && array_key_exists($key, $userData) ? $userData[$key] : false;
    }

    public function setUserData(array $data): void
    {
        $this->session->set("userData", $data);
    }

    public function removeUserData(): void
    {
        $this->session->remove("userData");
    }
}
