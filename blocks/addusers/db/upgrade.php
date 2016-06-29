<?php
function xmldb_block_addusers_upgrade($oldversion) {
	global $DB;
	$dbman = $DB->get_manager();
	$result = TRUE;
	
	if ($oldversion < 201606291104) {
		// Define table block_addusers_usercredits to be dropped.
		$table = new xmldb_table('block_addusers_usercredits');
		
		// Conditionally launch drop table for block_addusers_usercredits.
		if ($dbman->table_exists($table)) {
			$dbman->drop_table($table);
		}
		
		// Define table block_addusers_history to be dropped.
		$table = new xmldb_table('block_addusers_history');
		
		// Conditionally launch drop table for block_addusers_history.
		if ($dbman->table_exists($table)) {
			$dbman->drop_table($table);
		}
		
		// Define table block_addusers_createdusers to be dropped.
		$table = new xmldb_table('block_addusers_createdusers');
		
		// Conditionally launch drop table for block_addusers_createdusers.
		if ($dbman->table_exists($table)) {
			$dbman->drop_table($table);
		}
		
		// Define table block_addusers_groups to be created.
		$table = new xmldb_table('block_addusers_groups');
	
		// Adding fields to table block_addusers_groups.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('groupname', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table block_addusers_groups.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
		// Conditionally launch create table for block_addusers_groups.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
			
		// Define table block_addusers_usercredits to be created.
		$table = new xmldb_table('block_addusers_usercredits');
		
		// Adding fields to table block_addusers_usercredits.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('amount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
		
		// Adding keys to table block_addusers_usercredits.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('fk_adduser_usercredits_groupid', XMLDB_KEY_FOREIGN, array('groupid'), 'block_addusers_groups', array('id'));
		
		// Conditionally launch create table for block_addusers_usercredits.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		// Define table block_addusers_history to be created.
		$table = new xmldb_table('block_addusers_history');
		
		// Adding fields to table block_addusers_history.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('amount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('dateofpurchase', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('course_courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('user_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
		$table->add_field('comment', XMLDB_TYPE_TEXT, null, null, null, null, null);
		
		// Adding keys to table block_addusers_history.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('fk_history_course_courseid', XMLDB_KEY_FOREIGN, array('course_courseid'), 'course', array('id'));
		$table->add_key('history_user_userid', XMLDB_KEY_FOREIGN, array('user_userid'), 'user', array('id'));
		$table->add_key('fk_addusers_history_groupid', XMLDB_KEY_FOREIGN, array('groupid'), 'block_addusers_groups', array('id'));
		
		// Conditionally launch create table for block_addusers_history.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		// Define table block_addusers_createdusers to be created.
		$table = new xmldb_table('block_addusers_createdusers');
		
		// Adding fields to table block_addusers_createdusers.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('user_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('creator_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		$table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table block_addusers_createdusers.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('fk_creator_userid', XMLDB_KEY_FOREIGN, array('creator_userid'), 'user', array('id'));
		$table->add_key('fk_user_userid', XMLDB_KEY_FOREIGN, array('user_userid'), 'user', array('id'));
		$table->add_key('fk_block_addusers_createdusers_groupid', XMLDB_KEY_FOREIGN, array('groupid'), 'block_addusers_groups', array('id'));
		
		// Conditionally launch create table for block_addusers_createdusers.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		// Addusers savepoint reached.
		upgrade_block_savepoint(true, 201606291104, 'addusers');
	}
	return $result;
	
	
}
?>