<?php

// Global defaults.
define ( 'DEFAULT_COST', 3500 );
global $DB;
require_once $CFG->dirroot.'/group/lib.php';
require_once $CFG->dirroot.'/user/lib.php';

function block_addusers_get_credits($groupname) {
	global $DB;
	
	$groupid = block_addusers_get_groupid($groupname);
	
	
	$sql = "SELECT amount " .
			"FROM {block_addusers_usercredits} " .
			"WHERE groupid = ?";
	
	$field = $DB->get_record_sql($sql, array($groupid));
	
	if ($field == false) {
		block_addusers_add_group ( $groupname );
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
function block_addusers_add_credits($groupid, $credits, $comment, $studentid = '', $courseid = '') {
	global $DB;
	
	$usercredits = $DB->get_record ( 'block_addusers_usercredits', array (
			'groupid' => $groupid 
	) );
	
	$record = new stdClass ();
	$record->id = $usercredits->id;
	$record->groupid = $groupid;
	$record->amount = $credits + $usercredits->amount;
	
	$DB->update_record ( 'block_addusers_usercredits', $record );
	block_addusers_add_history($groupid, $credits, $courseid, $studentid, $comment);
}

/**
 * Add group to the database
 * @param unknown $grouponame
 */
function block_addusers_add_group($groupname) {
	global $DB;
	
	$record = new stdClass ();
	$record->groupname = $groupname;
	
	$groupid = $DB->insert_record ( 'block_addusers_groups', $record );
	
	$record = new stdClass ();
	$record->groupid = $groupid;
	$record->amount = '0';
	
	$DB->insert_record ( 'block_addusers_usercredits', $record );
	
	block_addusers_add_history ( $groupid, 0, '', '', '');
	
	return $groupid;
}

/**
 * Add purchase history
 * @param string $groupname
 * @param int $amount
 * @param string $courseid
 * @param string $studentid
 * @param string $comment
 */
function block_addusers_add_history($groupid, $amount, $courseid = '', $studentid = '', $comment = '') {
	global $DB;
	
	$record = new stdClass ();
	$record->groupid = $groupid;
	$record->amount = $amount;
	$record->dateofpurchase = time ();
	$record->course_courseid = $courseid;
	$record->user_userid = $studentid;
	$record->comment = $comment;
	
	$DB->insert_record ( 'block_addusers_history', $record );
}

function block_addusers_get_credit_history($groupid) {
	global $DB;
	
	$sql = "SELECT bah.id, bah.amount, bah.dateofpurchase, c.fullname AS coursename, u.firstname, u.lastname, bah.comment " .
			"FROM {block_addusers_history} bah " .
			"LEFT JOIN {course} c ON bah.course_courseid = c.id " .
			"LEFT JOIN {user} u ON bah.user_userid = u.id " .
			"WHERE bah.groupid = ? ORDER BY bah.dateofpurchase";
	
	return $DB->get_records_sql($sql, array($groupid));
}

function block_addusers_enroluser($userid, $courseid, $enddate, $groupname, $creatorid) {
	global $DB;
	
	if(block_addusers_checkuserenrolled($userid, $courseid))
		throw new Exception ( get_string ( 'already_enrolled', 'block_addusers' ) );
	
	$durationandprice = block_addusers_get_course_duration_and_costs( $courseid );
	
	$duration = $durationandprice->days - 1;
	$costs = $durationandprice->costs * 100;
	
	$credits = block_addusers_get_credits($groupname);
	
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
	$enrolment->enrol_user ( $instance, $userid, $instance->roleid, $startdate->getTimestamp (), $lastdate->getTimestamp () );
	$groupid = groups_get_group_by_name($courseid, $groupname);
	if($groupid == false)
	{
		$data = new stdClass();
		$data->courseid = $courseid;
		$data->name = $groupname;
		$data->description = $groupname;
		$groupid = groups_create_group($data);
	}
		
	groups_add_member($groupid, $userid);
		
		
	$payment = $costs * -1;
	block_addusers_add_credits(block_addusers_get_groupid($groupname), $payment, get_string('createuser', 'block_addusers'), $userid, $courseid);
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

function block_addusers_createuser($username, $firstname, $lastname, $email, $creatorid, $groupid) {
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
	$creator->groupid = $groupid;
	
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
function block_addusers_get_users_details($userids)
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
 * Get all the details from a single user with the given user id
 * @param int $userid
 */
function block_addusers_get_user_details($userid)
{
	global $DB;
	$user = $DB->get_record('user', array('id' => $userid));
	
	return $user;
}

/**
 * Update single user record
 * @param object with user
 */
function block_addusers_update_user_details($user)
{
	return user_update_user($user, false, false);
}

/**
 * Get all the users created by current user, optional with a name mask to search for specific users
 * @param int $creatorid
 * @param string $namesearch
 */
function block_addusers_get_users($groupid, $namesearch = '') {
	global $DB;
	
	$sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email 
			FROM {block_addusers_createdusers} cu 
			JOIN {user} u ON cu.user_userid = u.id 
			WHERE cu.groupid = :groupid 
			AND (u.firstname LIKE :search OR u.lastname LIKE :search2)
			AND u.deleted = 0";
	$params['groupid'] = $groupid;
	$params['search'] = '%' . $namesearch . '%';
	$params['search2'] = '%' . $namesearch . '%';
	
	return $DB->get_records_sql($sql, $params);
}

/**
 * Update all users to delete users deleted in moodle elsewhere
 * @param int $groupid
 */
function block_addusers_update_list($groupid) {
	global $DB;

	$sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
			FROM {block_addusers_createdusers} cu
			JOIN {user} u ON cu.user_userid = u.id
			WHERE cu.groupid = :groupid
			AND (u.firstname LIKE :search OR u.lastname LIKE :search2)";
	$params['groupid'] = $groupid;
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
	
	$sql = "SELECT DISTINCT g.id as groupid, uid.data as groupname, uc.amount
	FROM {block_addusers_usercredits} uc
	JOIN {block_addusers_groups} g ON g.id = uc.groupid
	RIGHT JOIN {user_info_data} uid ON uid.data = g.groupname
	RIGHT JOIN {user_info_field} uif ON uif.id = uid.fieldid
	JOIN {user} u on uid.userid = u.id
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

function block_addusers_get_groupid($groupname){
	global $DB;
	$sql = "SELECT id " .
			"FROM {block_addusers_groups} " .
			"WHERE groupname = ?";
	
	$field = $DB->get_record_sql($sql, array($groupname));
	
	if ($field == false) {
		return block_addusers_add_group ( $groupname );
	} else {
		return $field->id;
	}
}
