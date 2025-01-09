<?php
session_start();
include 'db.php';

if (!isset($_COOKIE['auth_token'])) {
    header("Location: login.html");
    exit();
}

$token = $_COOKIE['auth_token'];
$sql = "SELECT * FROM users WHERE auth_token='$token'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: login.html");
    exit();
}

include 'room.html';
?>