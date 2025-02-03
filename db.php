<?php
$host = "roundhouse.proxy.rlwy.net";
$dbname = "railway";
$username = "postgres";
$password = "KFSoLApbvxoaalKiOwvCTQOGuxceDjSv";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>