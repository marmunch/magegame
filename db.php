<?php

$host = 'pg';
$db = 'studs';
$user = 's333884';
$password = 'ig7dPUHXho4OLIJo';

$dsn = "pgsql:host=$host;port=5432;dbname=$db;";

try {
    $conn = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>