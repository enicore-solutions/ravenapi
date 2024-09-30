<?php

// finish this

class MySessionHandler implements SessionHandlerInterface {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($session_id) {
        $stmt = $this->pdo->prepare("SELECT session_data FROM php_sessions WHERE session_id = :session_id AND session_expire > NOW()");
        $stmt->execute(['session_id' => $session_id]);
        return $stmt->fetchColumn() ?: '';
    }

    public function write($session_id, $session_data) {
        $stmt = $this->pdo->prepare("REPLACE INTO php_sessions (session_id, session_data, session_expire) VALUES (:session_id, :session_data, DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
        return $stmt->execute(['session_id' => $session_id, 'session_data' => $session_data]);
    }

    public function destroy($session_id) {
        $stmt = $this->pdo->prepare("DELETE FROM php_sessions WHERE session_id = :session_id");
        return $stmt->execute(['session_id' => $session_id]);
    }

    public function gc($maxlifetime) {
        $stmt = $this->pdo->prepare("DELETE FROM php_sessions WHERE session_expire < NOW()");
        return $stmt->execute();
    }
}

// Setting up the session handler
$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
$handler = new MySessionHandler($pdo);
session_set_save_handler($handler, true);
session_start();

