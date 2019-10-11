<?php

namespace ChatApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "Server is listening on port 8080...\n";
    }

    public function onOpen(ConnectionInterface $conn){
        $this->clients->attach($conn);
        echo "New client is connected: Id = {$conn->resourceId};\n";
    }

    public function onMessage(ConnectionInterface $from, $msg){
        foreach($this->clients as $client){
            if($client !== $from){
                $client->send($msg);
            }
        }

    }

    public function onClose(ConnectionInterface $conn){
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected. \n";
    }

    public function onError(ConnectionInterface $conn, \Exception $ex){
        echo "An Error has occurred: {$ex->getMessage()}";
    }



}

?>