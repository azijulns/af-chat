<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
	protected $clients;
	protected $user_connections;
	protected $dbHandler;
	public function __construct() {
		$this->clients = new \SplObjectStorage;
		$this->user_connections = [];
	}

	public function onOpen(ConnectionInterface $conn) {
		// Store the new connection to send messages to later
		$this->clients->attach($conn);

		echo "New connection! ({$conn->resourceId})\n";
		// Store connection with user ID
		$params = $conn->httpRequest->getUri()->getQuery();
		parse_str($params, $query);
		$user_id = $query['user_id'] ?? null;

		if ($user_id) {
			$this->user_connections[$user_id] = $conn;
			echo "User $user_id connected.\n";
		}
	}

	public function onMessage(ConnectionInterface $from, $msg) {
		$data = json_decode($msg, true);

		// Validate data keys
		if (isset($data['sender_id'], $data['receiver_id'], $data['message'])) {
			$sender_id = $data['sender_id'];
			$receiver_id = $data['receiver_id'];
			$message_text = $data['message'];

			// Save message in the database using ChatDatabase
			$_data = [
				'sender_id' => $sender_id,
				'receiver_id' => $receiver_id,
				'message' => $message_text,
			];

			insert_data("af_chat_messages", $_data);



			if (isset($this->user_connections[$receiver_id])) {
				$receiver_conn = $this->user_connections[$receiver_id];
				$receiver_conn->send(json_encode([
					'sender_id' => $sender_id,
					'receiver_id' => $receiver_id,
					'message' => $message_text,
				]));
			}
		}

	}

	public function onClose(ConnectionInterface $conn) {
		// The connection is closed, remove it, as we can no longer send it messages
		$this->clients->detach($conn);

		echo "Connection {$conn->resourceId} has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}
}
