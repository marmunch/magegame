<?php
// poll.php

require 'db.php';

$room_id = isset($_GET['room_id']) ? $_GET['room_id'] : null;

if (!$room_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Неверные данные запроса']);
    exit;
}

// Проверка готовности всех игроков
$stmt = $pdo->prepare("SELECT COUNT(*) AS ready_count FROM Players WHERE id_game = ? AND cards_chosen = TRUE");
$stmt->execute([$room_id]);
$ready_count = $stmt->fetch()['ready_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_players FROM Players WHERE id_game = ?");
$stmt->execute([$room_id]);
$total_players = $stmt->fetch()['total_players'];

$allPlayersReady = ($ready_count == $total_players);

header('Content-Type: application/json');
echo json_encode(['type' => 'checkPlayersReady', 'allPlayersReady' => $allPlayersReady]);
?>
