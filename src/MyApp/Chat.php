<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        $answer = [
            "success" => true,
            "chatId" => $conn->resourceId
        ];

        $jsonResponse = json_encode($answer);
        $conn->send($jsonResponse);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);

        if (isset($data->message) && !empty($data->message)) {
            $string = $data->message;
            file_put_contents("message_{$from->resourceId}.txt", $string . "\n", FILE_APPEND);

            $answer = [
                "success" => true,
                "message" => "Ваше повідомлення отримано"
            ];

            $jsonResponse = json_encode($answer);
            $from->send($jsonResponse);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }
}
