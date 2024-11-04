<?php

// namespace MyApp;

// class ChatDatabase {
// 	protected $wpdb;
// 	protected $table_name;

// 	public function __construct() {
// 		global $wpdb;
// 		$this->wpdb = $wpdb;
// 		$this->table_name = $wpdb->prefix . 'chat_messages';

// 		$this->create_chat_table();
// 	}



// 	public function save_message($sender_id, $receiver_id, $message) {
// 		$this->wpdb->insert($this->table_name, [
// 			'sender_id' => $sender_id,
// 			'receiver_id' => $receiver_id,
// 			'message' => $message,
// 		]);
// 	}
// }
