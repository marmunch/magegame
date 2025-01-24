<?php
session_start();
include 'db.php';

function logError($message) {
    file_put_contents('error_log.txt', $message . PHP_EOL, FILE_APPEND);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_SESSION['login'];

    if (!$login) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не аутентифицирован']);
        exit;
    }

    try {
        // Начинаем транзакцию
        $conn->beginTransaction();

        // Создаем новую игру
        $stmt = $conn->prepare("INSERT INTO Games (status) VALUES (0)");
        if ($stmt->execute()) {
            $room_id = $conn->lastInsertId();

            // Добавляем создателя комнаты в таблицу Players
            $stmt = $conn->prepare("INSERT INTO Players (login, id_game) VALUES (:login, :id_game)");
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':id_game', $room_id);
            if ($stmt->execute()) {
                // Завершаем транзакцию
                $conn->commit();
                echo json_encode(['success' => true, 'room_id' => $room_id]);
            } else {
                // Откатываем транзакцию в случае ошибки
                $conn->rollBack();
                logError('Ошибка при добавлении игрока в комнату: ' . $stmt->errorInfo()[2]);
                echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении игрока в комнату']);
            }
        } else {
            // Откатываем транзакцию в случае ошибки
            $conn->rollBack();
            logError('Ошибка при создании комнаты: ' . $stmt->errorInfo()[2]);
            echo json_encode(['success' => false, 'message' => 'Ошибка при создании комнаты']);
        }
    } catch (PDOException $e) {
        // Откатываем транзакцию в случае исключения
        $conn->rollBack();
        logError('Ошибка базы данных: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
}
?>