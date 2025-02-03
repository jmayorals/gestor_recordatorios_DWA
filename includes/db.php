<?php
// includes/db.php

$host     = 'localhost';
$dbname   = 'gestor_recordatorios_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                   $username,
                   $password,
                   [
                     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                   ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
