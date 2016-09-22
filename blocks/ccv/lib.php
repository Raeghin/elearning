<?php
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/pdflib.php');

function block_ccv_get_groups()
{
	global $DB;
	
	$sql = "SELECT DISTINCT g.id as groupid, uid.data as groupname
	FROM {block_addusers_groups} g
	RIGHT JOIN {user_info_data} uid ON uid.data = g.groupname
	RIGHT JOIN {user_info_field} uif ON uif.id = uid.fieldid
	JOIN {user} u on uid.userid = u.id
	WHERE uif.shortname = 'Opleidernaam'";
	
	return ($DB->get_records_sql($sql));
}

function block_ccv_get_courses()
{
	global $DB;
	$sql = "SELECT c.id, c.fullname, c.shortname " .
			"FROM {course} c";

	return $DB->get_records_sql($sql);
}

function block_ccv_generate_report($groupid, $courseid, $report, $fromdate, $todate)
{
	switch ($report) {
		case 'a':
			$parameters = array('courseid' => $courseid, 'groupid' => $groupid, 'fromdate'=> $fromdate, 'todate'=>$todate);
			$url = new moodle_url('/blocks/ccv/generate_report_a.php', $parameters);
			$label = get_string('download');
			$options = array('class' => 'overviewButton');
			
			return single_button($url, $label, 'post', $options);
			
			break;
		case 'b':
			return 'b';
			break;
		case 'c':
			return 'c';
			break;
		case 'd':
			return 'd';
			break;
	}
}

/**
 * Gets the course context, allowing for old and new Moodle instances.
 *
 * @param int $courseid The course ID
 * @return stdClass The context object
 */
function block_ccv_get_course_context($courseid) {
	if (class_exists('context_course')) {
		return context_course::instance($courseid);
	} else {
		return get_context_instance(CONTEXT_COURSE, $courseid);
	}
}
