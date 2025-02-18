<?php
session_start();
include 'db.php';

if (!isset($_COOKIE['auth_token'])) {
    header("Location: login.html");
    exit();
}

$token = $_COOKIE['auth_token'];
$stmt = $conn->prepare("SELECT * FROM users WHERE auth_token = :token");
$stmt->bindParam(':token', $token);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    header("Location: login.html");
    exit();
}

include 'room.html';
?>