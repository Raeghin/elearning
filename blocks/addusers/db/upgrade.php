<?php
function xmldb_block_addusers_upgrade($oldversion) {
	global $DB;
	$dbman = $DB->get_manager();
	$result = TRUE;
	
	if ($oldversion < 201606271221) {
		
		// Define table block_addusers_history to be created.
		$table = new xmldb_table ( 'block_addusers_history' );
		
		// Adding fields to table block_addusers_history.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'customer_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'amount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'dateofpurchase', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'course_courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null );
		$table->add_field ( 'user_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null );
		$table->add_field ( 'comment', XMLDB_TYPE_TEXT, null, null, null, null, null );
		
		// Adding keys to table block_addusers_history.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		$table->add_key ( 'fk_history_user', XMLDB_KEY_FOREIGN, array (
				'customer_id' 
		), 'user', array (
				'id' 
		) );
		$table->add_key ( 'fk_history_course_courseid', XMLDB_KEY_FOREIGN, array (
				'course_courseid' 
		), 'course', array (
				'id' 
		) );
		$table->add_key ( 'history_user_userid', XMLDB_KEY_FOREIGN, array (
				'user_userid' 
		), 'user', array (
				'id' 
		) );
		
		// Conditionally launch create table for block_addusers_history.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Addusers savepoint reached.
		upgrade_block_savepoint ( true, 201606271222, 'addusers' );
	}
	
	return $result;
}
?>