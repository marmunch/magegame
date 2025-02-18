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
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
    
        $data = json_decode($msg, true);
        if ($data['type'] === 'startPhase2') {
            $room_id = $data['room_id'];
            // Отправляем сообщение всем клиентам о начале второй фазы
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'startPhase2', 'room_id' => $room_id]));
                    echo "Sent startPhase2 message to client {$client->resourceId}\n";
                }
            }
        } else if ($data['type'] === 'timer') {
            // Отправляем сообщение всем клиентам о таймере
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'timer', 'timeLeft' => $data['timeLeft']]));
                }
            }
        } else if ($data['type'] === 'checkPlayersReady') {
            $room_id = $data['room_id'];
            if (!isset($this->playersReady[$room_id])) {
                $this->playersReady[$room_id] = 0;
            }
            $this->playersReady[$room_id]++;
    
            // Проверяем, готовы ли все игроки
            $allPlayersReady = $this->playersReady[$room_id] >= 2; // Предполагаем, что в игре 2 игрока
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'checkPlayersReady', 'allPlayersReady' => $allPlayersReady]));
                }
            }
        } else if ($data['type'] === 'checkPhase2') {
            $room_id = $data['room_id'];
            // Отправляем сообщение всем клиентам для проверки начала второй фазы
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'checkPhase2', 'room_id' => $room_id]));
                    echo "Sent checkPhase2 message to client {$client->resourceId}\n";
                }
            }
        } else if ($data['type'] === 'playerReady') {
            $room_id = $data['room_id'];
            $login = $data['login'];
            // Отправляем сообщение всем клиентам о готовности игрока
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode(['type' => 'playerReady', 'room_id' => $room_id, 'login' => $login]));
                    echo "Sent playerReady message to client {$client->resourceId}\n";
                }
            }
        } else {
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send($msg);
                }
            }
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$loop = Factory::create();
$webSock = new Server('0.0.0.0:8080', $loop);

$webServer = new IoServer(
    new HttpServer(
        new WsServer(
            new GameServer()
        )
    ),
    $webSock,
    $loop
);
echo "WebSocket server is running on ws://localhost:8080/\n";
$loop->run();
