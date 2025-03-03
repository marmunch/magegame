<?php
require 'db.php'; 

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function send_message($data) {
    echo "data: " . json_encode($data) . "\n\n";
    flush();
}

function check_cards_chosen($conn, $room_id) {
    // Проверка, выбрали ли все игроки свои карты
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM Players WHERE id_game = ? AND cards_chosen = FALSE");
    $stmt->execute([$room_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] == 0) {
        // Все игроки выбрали свои карты
        send_message(['type' => 'startPhase2', 'room_id' => $room_id]);
    }
}

// Основной цикл SSE
$room_id = $_GET['room_id'];
while (true) {
    check_cards_chosen($conn, $room_id);
    sleep(5); 
}
?>
