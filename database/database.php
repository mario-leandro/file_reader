<?php

include __DIR__ . '/../common.php';

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
        resposta(["success" => false, "error" => "Database connection failed: " . $e->getMessage()]);
    }
}