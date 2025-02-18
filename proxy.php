<?php
$targetHost = 'se.ifmo.ru';
$targetPort = 8080;

$client = stream_socket_client("tcp://$targetHost:$targetPort", $errno, $errstr, 30);

if (!$client) {
    die("Failed to connect: $errstr ($errno)\n");
}

// Read the WebSocket handshake from the client
$handshake = fread($client, 1024);

// Forward the handshake to the target server
$target = stream_socket_client("tcp://$targetHost:$targetPort", $errno, $errstr, 30);

if (!$target) {
    die("Failed to connect to target: $errstr ($errno)\n");
}

fwrite($target, $handshake);

// Read the response from the target server
$response = fread($target, 1024);

// Forward the response to the client
fwrite($client, $response);

// Forward data between the client and the target server
while (true) {
    $read = [$client, $target];
    $write = null;
    $except = null;

    if (stream_select($read, $write, $except, null)) {
        foreach ($read as $socket) {
            $data = fread($socket, 1024);
            if ($data === false) {
                break 2;
            }
            if ($socket === $client) {
                fwrite($target, $data);
            } else {
                fwrite($client, $data);
            }
        }
    }
}

fclose($client);
fclose($target);
?>
