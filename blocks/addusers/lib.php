<?php

// Global defaults.
define ( 'DEFAULT_COST', 3500 );
global $DB;


function block_addusers_get_credits($userid) {
	global $DB;
	
	$field = $DB->get_record ( 'block_addusers_usercredits', array (
			'customer_id' => $userid 
	) );
	
	if ($field == false) {
		block_addusers_add_teacher ( $userid );
		return 0;
	} else {
		return $field->amount;
	}
}

/**
 * Add credits to a user
 *
 * @return array section information
 */
function block_addusers_add_credits($userid, $credits, $comment, $studentid = '', $courseid = '') {
	global $DB;
	
	$usercredits = $DB->get_record ( 'block_addusers_usercredits', array (
			'customer_id' => $userid 
	) );
	
	$record = new stdClass ();
	$record->id = $usercredits->id;
	$record->customer_id = $userid;
	$record->amount = $credits + $usercredits->amount;
	
	$DB->update_record ( 'block_addusers_usercredits', $record );
	block_addusers_add_history($userid, $credits, $courseid, $studentid, $comment);
}


function block_addusers_add_teacher($userid) {
	global $DB;
	
	$record = new stdClass ();
	$record->customer_id = $userid;
	$record->amount = '0';
	
	$DB->insert_record ( 'block_addusers_usercredits', $record );
	
	block_addusers_add_history ( $userid, 0, '', '', '');
}

function block_addusers_add_history($userid, $amount, $courseid = '', $studentid = '', $comment = '') {
	global $DB;
	
	$record = new stdClass ();
	$record->customer_id = $userid;
	$record->amount = $amount;
	$record->dateofpurchase = time ();
	$record->course_courseid = $courseid;
	$record->user_userid = $studentid;
	$record->comment = $comment;
	
	$DB->insert_record ( 'block_addusers_history', $record );
}
function block_addusers_get_credit_history($userid) {
	global $DB;
	
	$sql = "SELECT bah.id, bah.amount, bah.dateofpurchase, c.fullname AS coursename, u.firstname, u.lastname, bah.comment " .
			"FROM {block_addusers_history} bah " .
			"LEFT JOIN {course} c ON bah.course_courseid = c.id " .
			"LEFT JOIN {user} u ON bah.user_userid = u.id " .
			"WHERE bah.customer_id = ? ORDER BY bah.dateofpurchase";
	
	return $DB->get_records_sql($sql, array($userid));
}

function block_addusers_enroluser($userid, $courseid, $enddate, $creatorid) {
	global $DB;
	
	if(block_addusers_checkuserenrolled($userid, $courseid))
		throw new Exception ( get_string ( 'already_enrolled', 'block_addusers' ) );
	
	$durationandprice = block_addusers_get_course_duration_and_costs( $courseid );
	
	$duration = $durationandprice->days - 1;
	$costs = $durationandprice->costs * 100;
	
	$credits = block_addusers_get_credits($creatorid);
	
	if($credits < $costs){	
	 	throw new Exception ( get_string ( 'not_enough_credits', 'block_addusers' ) );
	}

	$startdate = new DateTime();
	$startdate->setTimeStamp( $enddate );
	$startdate->modify ( '-' . ($duration) . ' days' );
	
	$lastdate = new DateTime();
	$lastdate->setTimeStamp( $enddate );
	$lastdate->modify ( '+1 days' );
	
	$enrolment = enrol_get_plugin ( "manual" );
	
	$instance = get_instance_for_course ( $courseid );
	$enrol = $enrolment->enrol_user ( $instance, $userid, $instance->roleid, $startdate->getTimestamp (), $lastdate->getTimestamp () );
	
	$payment = $costs * -1;
	block_addusers_add_credits($creatorid, $payment, get_string('createuser', 'block_addusers'), $userid, $courseid);
}

function block_addusers_checkuseremail($email) {
	global $DB;
	if ($DB->count_records ( 'user', array (
			'email' => $email 
	) ) > 0)
		return true;
	else
		return false;
}

function block_addusers_checkusername($username) {
	global $DB;
	if ($DB->count_records ( 'user', array (
			'username' => $username 
	) ) > 0)
		return true;
	else
		return false;
}

function block_addusers_createuser($username, $firstname, $lastname, $email, $creatorid) {
	global $DB;
	if (block_addusers_checkuseremail ( $email ))
		throw new Exception ( get_string ( 'email_taken', 'block_addusers' ) );
	if ($username != $email)
		if (block_addusers_checkusername ( $username ))
			throw new Exception ( get_string ( 'username_taken', 'block_addusers' ) );
	
	$password = block_addusers_generateRandomString ( 8 );
	
	$user = new StdClass ();
	
	$user->auth = 'manual';
	$user->confirmed = 1;
	$user->mnethostid = 1;
	$user->email = $email;
	$user->username = $username;
	$user->password = md5 ( $password );
	$user->lastname = $lastname;
	$user->firstname = $firstname;
	
	$user->id = $DB->insert_record ( 'user', $user );
	
	$creator = new StdClass ();
	$creator->user_userid = $user->id;
	$creator->creator_userid = $creatorid;
	
	$DB->insert_record ( 'block_addusers_createdusers', $creator );
	
	if ($user->id > 0)
		return ( object ) [ 
				'success' => true,
				'message' => $password ,
				'userid' => $user->id
		];
	else
		throw new Exception ( get_string ( 'general_error', 'block_addusers' ) );
}

/**
 * Generate random string of numbers for password generation
 * @param number $length
 */
function block_addusers_generateRandomString($length = 8) {
	$characters = '23456789abcdefghjklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
	$charactersLength = strlen ( $characters );
	$randomString = '';
	for($i = 0; $i < $length; $i ++) {
		$randomString .= $characters [rand ( 0, $charactersLength - 1 )];
	}
	return $randomString;
}

/**
 * Get all the deatils from a user with the given user id(s)
 * @param array[int] $userids
 */
function block_addusers_get_user_details($userids)
{
	global $DB;
	$users = array();
	foreach ($userids as $userid)
	{
		$users[] = $DB->get_record('user', array('id' => $userid));
	}
	return $users;
}

/**
 * Get all the users created by current user, optional with a name mask to search for specific users
 * @param int $creatorid
 * @param string $namesearch
 */
function block_addusers_get_users($creatorid, $namesearch = '') {
	global $DB;
	
	$sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email FROM {block_addusers_createdusers} cu JOIN {user} u ON cu.user_userid = u.id WHERE cu.creator_userid = :creatorid AND (u.firstname LIKE :search OR u.lastname LIKE :search2)";
	$params['creatorid'] = $creatorid;
	$params['search'] = '%' . $namesearch . '%';
	$params['search2'] = '%' . $namesearch . '%';
	
	return $DB->get_records_sql($sql, $params);
}

/**
 * Get the courses the current user is enrolled to with the role of teacher or non-editing teacher
 * @param int $teacherid
 */
function block_addusers_get_eligable_courses($teacherid)
{
	global $DB;
	$sql = "SELECT t_course.id, t_course.fullname, t_course.shortname, br.costs, br.days " .
				"FROM {user} t_user " .
				"JOIN {user_enrolments} t_user_enrolments ON t_user_enrolments.userid = t_user.id " .
				"JOIN {enrol} t_enrol ON t_enrol.id = t_user_enrolments.enrolid " .
				"JOIN {course} t_course ON t_course.id = t_enrol.courseid ".
				"JOIN {role_assignments} t_role_assignments ON t_role_assignments.userid = t_user.id " .
				"JOIN {context} t_context ON (t_context.id = t_role_assignments.contextid AND t_context.instanceid = t_enrol.courseid) " .
				"JOIN {block_addusers_requirements} br ON br.course_courseid = t_course.id " .
				"WHERE t_context.contextlevel = '50' " .
				"AND (t_role_assignments.roleid = '3' OR t_role_assignments.roleid = '4') " .
				"AND t_user.id = ?";
	
	return $DB->get_records_sql($sql, array($teacherid));
}

/**
 * Get the courses the current user is enrolled to with the role of teacher or non-editing teacher
 * @param int $teacherid
 */
function block_addusers_get_course_duration_and_costs($courseid)
{
	global $DB;
	$sql = "SELECT costs, days " .
			"FROM {block_addusers_requirements} 
			WHERE course_courseid = ?";

	return $DB->get_record_sql($sql, array($courseid));
}

/**
 * Get the courses the user is enrolled in
 * @param int $userid
 */
function block_addusers_get_enrolled_courses($userid)
{
	global $DB;
	
	$sql = "SELECT t_course.id, t_course.fullname, t_course.shortname, t_user_enrolments.timestart, t_user_enrolments.timeend " .
			"FROM {user} t_user " .
			"JOIN {user_enrolments} t_user_enrolments ON t_user_enrolments.userid = t_user.id " .
			"JOIN {enrol} t_enrol ON t_enrol.id = t_user_enrolments.enrolid " .
			"JOIN {course} t_course ON t_course.id = t_enrol.courseid ".
			"JOIN {role_assignments} t_role_assignments ON t_role_assignments.userid = t_user.id " .
			"JOIN {context} t_context ON (t_context.id = t_role_assignments.contextid AND t_context.instanceid = t_enrol.courseid) " .
			"WHERE t_context.contextlevel = '50' " .
			"AND t_user.id = ?";

	return $DB->get_records_sql($sql, array($userid));
}


function block_addusers_get_courses()
{
	global $DB;
	$sql = "SELECT c.id, c.fullname, c.shortname, br.costs, br.days, br.id AS brid " .
			"FROM {course} c " .
			"LEFT JOIN {block_addusers_requirements} br ON br.course_courseid = c.id ORDER BY c.id";
	
	return $DB->get_records_sql($sql);
}

function block_addusers_save_price($courseid, $costs, $brid, $days)
{
	global $DB;
	
	$record = new stdClass ();
	
	$record->course_courseid = $courseid;
	$record->costs = $costs;
	$record->days = $days;
	
	if($DB->record_exists('block_addusers_requirements', array('id'=>$brid)))
	{
		$record->id = $brid;
		return $DB->update_record ( 'block_addusers_requirements', $record );
	} else {
		return $DB->insert_record( 'block_addusers_requirements', $record );
	}
}

function block_addusers_get_course_and_price($courseid)
{
	global $DB;
	$sql = "SELECT c.id, c.fullname, c.shortname, br.costs, br.days, br.id AS brid " .
			"FROM {course} c " .
			"LEFT JOIN {block_addusers_requirements} br ON br.course_courseid = c.id 
				WHERE c.id = ?";
	
	
	$record = $DB->get_record_sql($sql, array($courseid));
	
	if(!isset($record->brid))
	{
		block_addusers_save_price($courseid, 35, '', 10);
		return block_addusers_get_course_and_price($courseid);
	}
	else
		return $record;
}

function block_addusers_get_teachers()
{
	global $DB;
	$sql = "SELECT u.id, u.firstname, u.lastname, uc.amount, u.username, uif.shortname, uid.data
	FROM {user} AS u
	JOIN {user_info_data} AS uid ON uid.userid = u.id
	JOIN {user_info_field} AS uif ON uid.fieldid = uif.id
	LEFT JOIN {block_addusers_usercredits} uc ON uc.customer_id = u.id
	WHERE uif.shortname = 'Opleidernaam'";
	
	return ($DB->get_records_sql($sql));
	
}

/**
 * Get the instance of course
 * @param int $courseid id of course
 * @param bool $onlyenabled only return an enabled instance
 * @return object|bool $instance or false if not found
 */
function get_instance_for_course($courseid, $onlyenabled=true) {
	global $DB;
	$params = array('enrol' => 'manual', 'courseid' => $courseid);
	if (!empty($onlyenabled)) {
		$params['status'] = ENROL_INSTANCE_ENABLED;
	}
	return $DB->get_record('enrol', $params);
}

function block_addusers_checkuserenrolled($userid, $courseid)
{
	global $DB;
	return is_enrolled($context = context_course::instance($courseid), $userid, '', false);
}

