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


            if (isset($data->to) && !empty($data->to)) {
                $this->sendMessageToResourceID($from->resourceId, $data->to, $data->message);
            }
        }
        if (isset($data->getAllUsers)) {

            $arr = [];

            foreach ($this->clients as $client) {
                $arr[] = [
                    "id" => $client->resourceId
                ];
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

    public function sendMessageToResourceID($from, $to, $message)
    {

        $answer = [
            "success" => true,
            "from" => $from,
            "to" => $to,
            "type" => 'in',
            "message" => $message
        ];

        $jsonResponse = json_encode($answer);

        foreach ($this->clients as $client) {
            if ($client->resourceId == $to) {
                $client->send($jsonResponse);
                return true;
            }
        }

        return false;
    }
}
