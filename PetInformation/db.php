<?php
define('DB_HOST',    'localhost');
define('DB_NAME',    'pets_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
                DB_USER, DB_PASS, $options
            );

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . DB_NAME . "`");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS pets (
                    id          INT AUTO_INCREMENT PRIMARY KEY,
                    name        VARCHAR(100)  NOT NULL,
                    species     VARCHAR(50)   NOT NULL,
                    breed       VARCHAR(100)  NOT NULL,
                    age         DECIMAL(4,1)  NOT NULL,
                    owner_name  VARCHAR(100)  NOT NULL,
                    description TEXT,
                    image       VARCHAR(255)  DEFAULT NULL,
                    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
                )
            ");

        } catch (PDOException $e) {
            die('<div class="alert alert-danger m-4">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }
    return $pdo;
}
?>