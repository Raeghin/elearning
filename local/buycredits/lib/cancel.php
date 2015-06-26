<?php
	require_once('/../../../config.php');
	require_once('base.php');
	
	global $DB, $USER, $PAGE; 
	$PAGE->set_url(new moodle_url('/local/buycredits/lib/cancel.php')); 
	$PAGE->set_context(context_system::instance());
	$PAGE->set_pagelayout('standard');
	
	$gdscredit = new gds_credit(array(
		'config' => $CFG,
		'user' => $USER
	));
	
	$gdscredit->controller('index/paymenterror', 'credit')
			->run();
	