<?php

function CreateSocket($addr, $port)
{
    $socket = null;
    if (!$socket = socket_create(AF_INET, SOCK_DGRAM, 0)) { return null; }
    if (!socket_set_nonblock($socket)) { return null; }
    if (!socket_bind($socket, "0.0.0.0", 0)) { return null; }
    if (!socket_connect($socket, $addr, $port)) { return null; }
    return $socket;
}

function CreatePacket($liveID)
{
    $payload = "\x00" . chr(strlen($liveID)) . $liveID . "\x00";
    $header  = "\xdd\x02\x00" . chr(strlen($payload)) . "\x00\x00";
    $packet  = $header . $payload;
    return $packet;
}

function SendPacket($socket, $packet)
{
    for ($i = 0; $i < 10; $i ++)
    {
        socket_send($socket, $packet, strlen($packet), 0);
    }
}

function ExecInit()
{
    $XBOX_PORT = 5050;
    $XBOX_PING = "dd00000a000000000000000400000002";

    $addr = $_POST["Address"];
    $id = $_POST["LiveID"];

    $socket = CreateSocket($addr, $XBOX_PORT);
    if ($socket === null)
    {
        return -1;
    }

    $packet = CreatePacket($id);

    SendPacket($socket, $packet);

    socket_close($socket);
    return ["Success" => true];
}

?>
