<?php
session_start();
include 'db.php';

use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use React\Promise\Promise;

header('Content-Type: application/json');

$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : null;
$new_time = isset($_POST['new_time']) ? intval($_POST['new_time']) : null;

if (!$room_id || !$new_time) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные запроса']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE Games SET current_timee = :new_time WHERE id_game = :id_game");
    $stmt->bindParam(':new_time', $new_time, PDO::PARAM_INT);
    $stmt->bindParam(':id_game', $room_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Время обновлено']);

       
        require __DIR__ . '/vendor/autoload.php';

        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector('ws://localhost:8080')
            ->then(function(WebSocket $conn) use ($new_time) {
                $conn->send(json_encode(['type' => 'timer', 'timeLeft' => $new_time]));
                $conn->close();
            }, function(\Exception $e) use ($loop) {
                echo 'Could not connect: ' . $e->getMessage();
                $loop->stop();
            });

        $loop->run();
    } else {
        throw new Exception('Ошибка обновления времени');
    }
} catch (PDOException $e) {
    error_log("Ошибка базы данных: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
