<?php

defined('ABSPATH') || die();



/**
 * Creates a new table in the WordPress database.
 *
 * @param string $tableName The name of the table to be created.
 * @param array $columns An array of column definitions. Each column definition is an associative array with keys for the column name, data type, and any additional constraints.
 * @return void
 *
 * @throws \Exception If the table creation fails.
 *
 * @since 1.0.0
 */
function create_table($tableName, $columns) {
	// Access the global $wpdb object to interact with the database.
	global $wpdb;

	// Get the charset and collation for the database.
	$charset_collate = $wpdb->get_charset_collate();

	// Generate the full table name with the WordPress prefix.
	$table_name = $wpdb->prefix . $tableName;

	// Construct the SQL statement for creating the table.
	$sql = "CREATE TABLE $table_name (\n";

	// Loop through columns and add them to the SQL statement.
	foreach ($columns as $column) {
		$sql .= "{$column['name']} {$column['type']}";

		// Add additional constraints if present.
		if (isset($column['constraints'])) {
			$sql .= " {$column['constraints']}";
		}

		$sql .= ",\n";
	}

	// Ensure 'id' is specified as an auto-increment primary key.
	$sql .= "PRIMARY KEY  (id)\n";

	// Close the SQL statement.
	$sql .= ") $charset_collate;";

	// Include the upgrade.php file to use the dbDelta function.
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Execute the SQL statement using the dbDelta function.
	dbDelta($sql);
}

// Usage:
// Define the column definitions.

// Activation hook function


/**
 * Inserts data into a specified WordPress table and saves the data in JSON format.
 *
 * @param string $tableName The name of the table to insert data into.
 * @param array $data An associative array containing the data to be inserted.
 *
 * @return int|false The ID of the inserted row or false if the insertion failed.
 *
 * @throws \Exception If the insertion fails and an error message is available.
 *
 * @since 1.0.0
 */
function insert_data($tableName, $data) {
	global $wpdb;

	// Sanitize the table name to prevent SQL injection.
	$table_name = sanitize_text_field($wpdb->prefix . $tableName);

	// Validate that $data is an array and not empty.
	if (!is_array($data) || empty($data)) {
		throw new \Exception("Invalid data provided for insertion.");
	}

	// Sanitize each item in the $data array.
	$sanitized_data = array_map('sanitize_text_field', $data);

	// Insert the sanitized data into the table.
	$result = $wpdb->insert($table_name, $sanitized_data);

	// Check if the insertion was successful.
	if ($result === false) {
		// Get last error message from $wpdb
		$error_message = $wpdb->last_error;
		throw new \Exception("Failed to insert data into the table: $tableName. Error: $error_message");
	}

	// Return the ID of the inserted row.
	return $wpdb->insert_id;
}

