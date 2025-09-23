<?php

require_once '../configs.php';

function getConnectionDB() {
    try {
        $db_connection = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4;port=" . DB_PORT,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        return $db_connection;
    } catch (PDOException $e) {
        resposta(500, ["success" => false, "error" => "Database connection failed: " . $e->getMessage()]);
    }
}