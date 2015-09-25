<?php
function xmldb_local_buycredits_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    /// Add a new column newcol to the mdl_myqtype_options
    if ($oldversion < 201507271503) {

        // Define field transactionid to be added to local_usercreditshistory.
        $table = new xmldb_table('local_usercreditshistory');
        $field = new xmldb_field('transactionid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'dateofpurchase');

        // Conditionally launch add field transactionid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Buycredits savepoint reached.
        upgrade_plugin_savepoint(true, 201507271503, 'local', 'buycredits');
    }
	
	if ($oldversion < 201507291341) {
		 

        // Define table local_createdusers to be created.
        $table = new xmldb_table('local_createdusers');

        // Adding fields to table local_createdusers.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('user_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('creator_userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_createdusers.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_creator_userid', XMLDB_KEY_FOREIGN, array('creator_userid'), 'user', array('id'));
        $table->add_key('fk_user_userid', XMLDB_KEY_FOREIGN, array('user_userid'), 'user', array('id'));

        // Conditionally launch create table for local_createdusers.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Buycredits savepoint reached.
        upgrade_plugin_savepoint(true, 201507291341, 'local', 'buycredits');
	}

    if ($oldversion < 201509080950) {

        // Define table local_createdusers to be created.
        $table = new xmldb_table('local_courserequirements');

        // Adding fields to table local_createdusers.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course_courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('credits', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, null);
        $table->add_field('days', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_createdusers.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_course_courseid', XMLDB_KEY_FOREIGN, array('course_courseid'), 'course', array('id'));

        // Conditionally launch create table for local_createdusers.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Buycredits savepoint reached.
        upgrade_plugin_savepoint(true, 201509080950, 'local', 'buycredits');
    }
	
    return true;
}
?>