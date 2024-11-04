<?php

/**
 * Plugin Name: AF Chat
 * Description: A chat application using WebSocket and Ratchet
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: af-chat
 */

defined('ABSPATH') || exit;

define('AF_CHAT_VERSION', '1.0.0');
define('AF_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AF_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AF_CHAT_ASSETS', trailingslashit(AF_CHAT_PLUGIN_URL . 'assets'));

if (!class_exists('AF_Chat_Main')):

	final class AF_Chat_Main {
		private static $instance;

		private function __construct() {
			$this->includes();
			$this->init_hooks();
		}

		public static function instance() {
			if (!isset(self::$instance) && !(self::$instance instanceof AF_Chat_Main)) {
				self::$instance = new AF_Chat_Main();
			}
			return self::$instance;
		}



		private function includes() {
			// require_once AF_CHAT_PLUGIN_DIR . 'includes/class-chat-database.php';
		}

		private function init_hooks() {
			register_activation_hook(__FILE__, ['AF_Chat_Main', 'start_chat_server']);
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
			add_shortcode('af_chat_box', [$this, 'af_chat_message_form']);
		}
		public function af_chat_message_form() {
			ob_start();
?>

			<div id="af-chat-container">
				<div id="af-chat-messages"></div>
				<input type="hidden" value="<?php echo get_current_user_id()?>" id="af-chat-user_id">
				<!-- Dropdown for selecting recipient -->
				<select id="af-chat-recipient">
					<option value="">Select User</option>
					<?php
					$users = get_users(['exclude' => [get_current_user_id()]]); // Exclude current user
					foreach ($users as $user) {
						echo "<option value='{$user->ID}'>{$user->display_name}</option>";
					}
					?>
				</select>

				<form id="af-chat-form">
					<input type="text" id="af-chat-input" placeholder="Type your message..." required />
					<button type="submit">Send</button>
				</form>
			</div>

			<script>
				jQuery(document).ready(function() {
					// Update the recipient ID based on dropdown selection
					jQuery("#af-chat-recipient").on("change", function() {
						receiverId = jQuery(this).val(); // Set receiverId dynamically
					});
				});
			</script>
<?php
			return ob_get_clean();
		}

		public static function start_chat_server() {
			global $wpdb;
			require_once AF_CHAT_PLUGIN_DIR . 'includes/functions.php';
			$phpPath = PHP_BINARY;
			$command = "$phpPath " . AF_CHAT_PLUGIN_DIR . "bin/chat-server.php > /dev/null 2>&1 & echo $!";
			exec($command);

			$columns = [
				['name' => 'id', 'type' => 'int(9)', 'constraints' => 'NOT NULL AUTO_INCREMENT'],
				['name' => 'sender_id', 'type' => 'int(9)', 'constraints' => 'NOT NULL'],
				['name' => 'receiver_id', 'type' => 'int(9)', 'constraints' => 'NOT NULL'],
				['name' => 'message', 'type' => 'text', 'constraints' => 'NOT NULL'],
				['name' => 'timestamp', 'type' => 'TIMESTAMP', 'constraints' => 'DEFAULT CURRENT_TIMESTAMP']
			];

			// Create the table with dynamic column definitions.
			create_table('af_chat_messages', $columns);
		}

		public function enqueue_scripts() {
			wp_enqueue_script('af-chat-client', AF_CHAT_ASSETS . 'js/websocket-client.js', [], null, true);
			wp_enqueue_style('af-chat-style', AF_CHAT_ASSETS . 'css/chat-style.css', [], null);
		}
	}

endif;

// Initialize the plugin
function af_chat_init_plugin() {
	return AF_Chat_Main::instance();
}
af_chat_init_plugin();
