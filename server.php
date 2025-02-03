<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\SecureServer;
use React\Socket\Server;

class GameServer implements MessageComponentInterface {
    protected $clients;
    protected $playersReady;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->playersReady = [];
        error_log("GameServer initialized.");
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        error_log("New connection! ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        error_log(sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's'));

        $data = json_decode($msg, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid JSON received: $msg");
            return;
        }

        if (!isset($data['type'])) {
            error_log("Message type is missing: $msg");
            return;
        }

        switch ($data['type']) {
            case 'startPhase2':
                $room_id = $data['room_id'] ?? null;
                if ($room_id === null) {
                    error_log("Room ID is missing in startPhase2 message.");
                    return;
                }
                $this->broadcast($from, json_encode(['type' => 'startPhase2', 'room_id' => $room_id]));
                break;

            case 'timer':
                $this->broadcast($from, json_encode(['type' => 'timer', 'timeLeft' => $data['timeLeft']]));
                break;

            case 'checkPlayersReady':
                $room_id = $data['room_id'] ?? null;
                if ($room_id === null) {
                    error_log("Room ID is missing in checkPlayersReady message.");
                    return;
                }
                if (!isset($this->playersReady[$room_id])) {
                    $this->playersReady[$room_id] = 0;
                }
                $this->playersReady[$room_id]++;

                $allPlayersReady = $this->playersReady[$room_id] >= 2;
                $this->broadcast($from, json_encode(['type' => 'checkPlayersReady', 'allPlayersReady' => $allPlayersReady]));
                break;

            case 'checkPhase2':
                $room_id = $data['room_id'] ?? null;
                if ($room_id === null) {
                    error_log("Room ID is missing in checkPhase2 message.");
                    return;
                }
                $this->broadcast($from, json_encode(['type' => 'checkPhase2', 'room_id' => $room_id]));
                break;

            case 'playerReady':
                $room_id = $data['room_id'] ?? null;
                $login = $data['login'] ?? null;
                if ($room_id === null || $login === null) {
                    error_log("Room ID or login is missing in playerReady message.");
                    return;
                }
                $this->broadcast($from, json_encode(['type' => 'playerReady', 'room_id' => $room_id, 'login' => $login]));
                break;

            default:
                $this->broadcast($from, $msg);
                break;
        }
    }

    private function broadcast(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
                error_log("Sent message to client {$client->resourceId}");
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        error_log("Connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("An error has occurred: {$e->getMessage()}");
        $this->clients->detach($conn);
        $conn->close();
    }
}

$loop = Factory::create();

error_log("Starting WebSocket server...");

$webSock = new Server('0.0.0.0:8081', $loop);

$webServer = new IoServer(
    new HttpServer(
        new WsServer(
            new GameServer()
        )
    ),
    $webSock,
    $loop
);

error_log("WebSocket server is running on wss://0.0.0.0:8081/");
$loop->run();
