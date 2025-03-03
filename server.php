<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
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
        if ($data['type'] === 'startPhase2') {
            $room_id = $data['room_id'];
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'startPhase2', 'room_id' => $room_id]));
                    error_log("Sent startPhase2 message to client {$client->resourceId}");
                }
            }
        } else if ($data['type'] === 'timer') {
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'timer', 'timeLeft' => $data['timeLeft']]));
                    error_log("Sent timer message to client {$client->resourceId}");
                }
            }
        } else if ($data['type'] === 'checkPlayersReady') {
            $room_id = $data['room_id'];
            if (!isset($this->playersReady[$room_id])) {
                $this->playersReady[$room_id] = 0;
            }
            $this->playersReady[$room_id]++;

            $allPlayersReady = $this->playersReady[$room_id] >= 2; 
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'checkPlayersReady', 'allPlayersReady' => $allPlayersReady]));
                    error_log("Sent checkPlayersReady message to client {$client->resourceId}");
                }
            }
        } else if ($data['type'] === 'checkPhase2') {
            $room_id = $data['room_id'];
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'checkPhase2', 'room_id' => $room_id]));
                    error_log("Sent checkPhase2 message to client {$client->resourceId}");
                }
            }
        } else if ($data['type'] === 'playerReady') {
            $room_id = $data['room_id'];
            $login = $data['login'];
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'playerReady', 'room_id' => $room_id, 'login' => $login]));
                    error_log("Sent playerReady message to client {$client->resourceId}");
                }
            }
        } else {
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send($msg);
                    error_log("Sent message to client {$client->resourceId}");
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        error_log("Connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("An error has occurred: {$e->getMessage()}");
        $conn->close();
    }
}

$loop = Factory::create();


$port = getenv('PORT') ?: 8080;

error_log("Starting WebSocket server...");

$webSock = new Server("0.0.0.0:{$port}", $loop);

$webServer = new IoServer(
    new HttpServer(
        new WsServer(
            new GameServer()
        )
    ),
    $webSock,
    $loop
);

error_log("WebSocket server is running on ws://0.0.0.0:{$port}/");
$loop->run();
