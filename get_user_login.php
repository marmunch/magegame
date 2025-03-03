<?php
session_start();
if (isset($_SESSION['login'])) {
    echo json_encode(['login' => $_SESSION['login']]);
} else {
    echo json_encode(['login' => 'Guest']);
}
?>

