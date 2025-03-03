<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $login = $_SESSION['login'];

    if (!$login) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не аутентифицирован']);
        exit;
    }

    try {
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Players WHERE login = :login AND id_game = :id_game");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':id_game', $room_id);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            
            echo json_encode(['success' => true]);
            exit;
        }

        $conn->beginTransaction();

        
        $stmt = $conn->prepare("INSERT INTO Players (login, id_game) VALUES (:login, :id_game)");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':id_game', $room_id);
        $stmt->execute();

        
        $stmt = $conn->prepare("UPDATE Invitations SET status = 'accepted' WHERE invited_login = :login AND room_id = :room_id");
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':room_id', $room_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>
