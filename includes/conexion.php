<?php
date_default_timezone_set("America/Bogota");

$host = 'localhost';
$db   = 'registro_pacientes';
$user = 'root';
$pass = 'carlos12';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $conn = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
