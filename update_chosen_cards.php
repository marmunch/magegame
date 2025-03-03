<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : null;
$card_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : null;
$card_type = isset($_POST['card_type']) ? intval($_POST['card_type']) : null;
$action = isset($_POST['action']) ? $_POST['action'] : null;

if (!$room_id || !$card_id || !$card_type || !$action) {
    echo json_encode(['success' => false, 'message' => 'Неверные параметры запроса']);
    exit;
}

try {
    if ($action === 'add') {
        
        $stmt = $conn->prepare("INSERT INTO Spells (id_card, id_player, id_game, card_position) VALUES (:card_id, :id_player, :id_game, :card_position)");
        $stmt->bindParam(':card_id', $card_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_player', $_SESSION['player_id'], PDO::PARAM_INT);
        $stmt->bindParam(':id_game', $room_id, PDO::PARAM_INT);
        $stmt->bindParam(':card_position', $card_type, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Ошибка добавления карты в chosen_cards');
        }
    } elseif ($action === 'remove') {
        
        $stmt = $conn->prepare("DELETE FROM Spells WHERE id_card = :card_id AND id_player = :id_player AND id_game = :id_game AND card_position = :card_position");
        $stmt->bindParam(':card_id', $card_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_player', $_SESSION['player_id'], PDO::PARAM_INT);
        $stmt->bindParam(':id_game', $room_id, PDO::PARAM_INT);
        $stmt->bindParam(':card_position', $card_type, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Ошибка удаления карты из chosen_cards');
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Неверное действие']);
        exit;
    }
} catch (PDOException $e) {
    error_log("Ошибка базы данных: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
