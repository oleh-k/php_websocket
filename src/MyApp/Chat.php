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


            if (isset($data->id) && !empty($data->id)) {
                $this->sendMessageToResourceID($data->id, $data->message);
            }
        }
        if (isset($data->getAllUsers)) {

            $arr = [];

            foreach ($this->clients as $client) {
                $arr[] = $client->resourceId;
            }

            $answer = [
                "users" => $arr
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

    public function sendMessageToResourceID($resourceId, $message)
    {

        $answer = [
            "success" => true,
            "message" => $message
        ];

        $jsonResponse = json_encode($answer);

        foreach ($this->clients as $client) {
            if ($client->resourceId == $resourceId) {
                $client->send($jsonResponse);
                return true;
            }
        }

        return false;
    }
}
