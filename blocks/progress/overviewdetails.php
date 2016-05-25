<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Progress Bar block overview page
 *
 * @package contrib
 * @subpackage block_progress
 * @copyright 2016 Egbert Minnaar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include required files.
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/progress/lib.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/pdflib.php');

define ( 'USER_SMALL_CLASS', 20 ); // Below this is considered small.
define ( 'USER_LARGE_CLASS', 200 ); // Above this is considered large.
define ( 'DEFAULT_PAGE_SIZE', 20 );
define ( 'SHOW_ALL_PAGE_SIZE', 5000 );

// Gather form data.
$id = required_param ( 'id', PARAM_INT );
$courseid = required_param ( 'courseid', PARAM_INT );
$page = optional_param ( 'page', 0, PARAM_INT ); // Which page to show.
$userid = required_param ( 'userid', PARAM_INT );

// Get specific block config and context.
$progressblock = $DB->get_record ( 'block_instances', array (
		'id' => $id 
), '*', MUST_EXIST );
$progressconfig = unserialize ( base64_decode ( $progressblock->configdata ) );
$progressblockcontext = block_progress_get_block_context ( $id );

// Determine course and context.
$course = $DB->get_record ( 'course', array (
		'id' => $courseid 
), '*', MUST_EXIST );
$context = block_progress_get_course_context ( $courseid );

$user = $DB->get_record ( 'user', array (
		'id' => $userid 
), '*', MUST_EXIST );

// Set up page parameters.
$PAGE->set_course ( $course );
$PAGE->requires->css ( '/blocks/progress/styles.css' );
$PAGE->set_url ( '/blocks/progress/overviewdetails.php', array (
		'courseid' => $courseid,
		'page' => $page,
		'userid' => $userid 
) );
$PAGE->set_context ( $context );
$title = get_string ( 'student_details', 'block_progress', $user->firstname . ' ' . $user->lastname );
$PAGE->set_title ( $title );
$PAGE->set_heading ( $title );
$PAGE->navbar->add ( $title );
$PAGE->set_pagelayout ( 'report' );

// Check user is logged in and capable of accessing the Overview.
require_login ( $course, false );
require_capability ( 'block/progress:overview', $progressblockcontext );

// Start page output.
echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title, 2 );
echo $OUTPUT->container_start ( 'block_progress' , 'overview_data');


// Setup submissions table.
$table = new flexible_table ( 'mod-block-progress-overviewdetails' );
$tablecolumns = array (
		'description',
		'value' 
);
$table->define_columns ( $tablecolumns );

$table->set_attribute ( 'class', 'overviewTable' );
$table->column_style_all ( 'padding', '5px' );
$table->column_style_all ( 'text-align', 'left' );
$table->column_style_all ( 'vertical-align', 'middle' );
$table->column_style ( 'details', 'width', '5%' );

$table->define_baseurl ( $PAGE->url );
$table->setup ();

$tableheaders = array (
		get_string ( 'details', 'block_progress' ),
		'' 
);
$table->define_headers ( $tableheaders );
$table->define_baseurl ( $PAGE->url );
$table->setup ();

$rows = array ();

$rows [] = array (
		'description' => get_string ( 'fullname' ),
		'value' => $user->firstname . ' ' . $user->lastname 
);

$rows [] = array (
		'description' => get_string ( 'course' ),
		'value' => $course->fullname 
);

// Get Last Online Time
$sql = "SELECT COALESCE(l.timeaccess, 0) AS lastonlinetime
FROM {user} u
JOIN {role_assignments} a ON (a.contextid = :contextid AND a.userid = u.id)
LEFT JOIN {user_lastaccess} l ON (l.courseid = :courseid AND l.userid = u.id)
		WHERE u.id = :userid";
$params ['contextid'] = $context->id;
$params ['courseid'] = $course->id;
$params ['userid'] = $user->id;
$lastonlinetime = $DB->get_record_sql ( $sql, $params )->lastonlinetime;

if (empty ( $lastonlinetime )) {
	$lastonline = get_string ( 'never' );
} else {
	$lastonline = userdate ( $lastonlinetime );
}

$rows [] = array (
		'description' => get_string ( 'lastonline', 'block_progress' ),
		'value' => $lastonline 
);

$timespent = utils::format_timespend ( block_progress_get_timespent ( $user->id, true, $course->id ) );

$rows [] = array (
		'description' => get_string ( 'timespent', 'block_progress' ),
		'value' => $timespent 
);

// Add data to table.
foreach ( $rows as $row ) {
	$table->add_data ( array (
			$row ['description'],
			$row ['value'] 
	) );
}

$table->print_html ();

// Setup details table.
$detailstable = new flexible_table ( 'mod-block-progress-overviewdetails-hours' );

$tablecolumns = array (
		'number',
		'start',
		'end',
		'time' 
);
$detailstable->define_columns ( $tablecolumns );

$detailstable->set_attribute ( 'class', 'overviewTable' );
$detailstable->column_style_all ( 'padding', '5px' );
$detailstable->column_style_all ( 'text-align', 'left' );
$detailstable->column_style_all ( 'vertical-align', 'middle' );
$detailstable->column_style ( 'number', 'width', '7%' );
$detailstable->column_style ( 'start', 'width', '31%' );
$detailstable->column_style ( 'end', 'width', '31%' );
$detailstable->column_style ( 'time', 'width', '31%' );

$detailstable->define_baseurl ( $PAGE->url );
$detailstable->setup ();

$tableheaders = array (
		'#',
		get_string ( 'start', 'block_progress' ),
		get_string ( 'end', 'block_progress' ),
		get_string ( 'time', 'block_progress' ) 
);

$detailstable->define_headers ( $tableheaders );
$detailstable->define_baseurl ( $PAGE->url );
$detailstable->setup ();

$rows = array ();

$timetable = block_progress_get_timespent ( $user->id, false, $course->id );

$count = 0;
foreach ( $timetable as $time ) {
	$count ++;
	$rows [] = array (
			'number' => $count,
			'start' => userdate ( $time->start_date ),
			'end' => userdate ( $time->start_date + $time->timespendtime ),
			'time' => utils::format_timespend ( $time->timespendtime ) 
	);
}

// Add data to table.
foreach ( $rows as $row ) {
	$detailstable->add_data ( array (
			$row ['number'],
			$row ['start'],
			$row ['end'],
			$row ['time'] 
	) );
}

$detailstable->print_html ();

$parameters = array('courseid' => $course->id, 'userid' => $user->id);
$url = new moodle_url('/blocks/progress/generatepdfreport.php', $parameters);
$label = get_string('download');
$options = array('class' => 'overviewButton');

echo $OUTPUT->single_button($url, $label, 'post', $options);

echo $OUTPUT->container_end ();
echo $OUTPUT->footer ();



