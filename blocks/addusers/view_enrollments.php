<?php

// Include required files.
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/addusers/lib.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/formslib.php');

class enrolluserform extends moodleform {
	function definition() {
		global $CFG;
		
		$mform = $this->_form;
		
		
		
		$mform->addElement ( 'date_selector', 'practicaldate', get_string ( 'practicalday', 'block_addusers' ), array (
				'startyear' => 2010,
				'stopyear' => 2025,
				'optional' => false 
		) );
		$mform->setDefault ( 'practicaldate', date ( 'U', strtotime ( "+10 days" ) ) );
		
		$mform->addElement ( 'submit', 'submitbutton', get_string ( 'enroll', 'block_addusers' ) );
		
		$mform->setType ( 'userid', PARAM_RAW );
		$mform->addElement ( 'hidden', 'userid', $this->_customdata ['userid'] );
		
		$mform->setType ( 'courseid', PARAM_RAW );
		$mform->addElement ( 'hidden', 'courseid', $this->_customdata ['courseid'] );
		
		$mform->setType ( 'submitted', PARAM_RAW );
		$mform->addElement ( 'hidden', 'submitted', 1 );
		
		
	}
}

// Gather form data.
$userid = required_param ( 'userid', PARAM_INT );
$courseid = optional_param ( 'courseid', '-1', PARAM_INT );
$showform = optional_param ( 'showform', '0', PARAM_INT );
$submitted = optional_param ( 'submitted', 0, PARAM_INT );

$PAGE->set_url ( '/blocks/addusers/view_enrollmenst.php', array () );
$PAGE->requires->css ( '/blocks/addusers/styles.css' );

require_login ( 0, false );
$PAGE->set_context ( context_system::instance () );

$user = block_addusers_get_user_details ( array (
		$userid 
) ) [0];

if ($showform)
{
	$data = new stdClass();
	$data->firstname = $user->firstname;
	$data->lastname = $user->lastname;
	$data->coursename = get_course($courseid)->fullname;
	$title = get_string ( 'enroll_user', 'block_addusers', $data);
}
else
	$title = '';

$PAGE->set_title ( $title );
$PAGE->set_heading ( get_string ( 'details', 'block_addusers' ) );
$PAGE->navbar->add ( $title );
$PAGE->set_pagelayout ( 'standard' );

// Start page output.
echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title, 2 );
echo $OUTPUT->container_start ( 'block_listusers' );


if($showform || $submitted)
	$form = new enrolluserform ( null, array (
			'userid' => $userid,
			'courseid' => $courseid
	) );
if ($showform)
{
	echo '<b>' . get_string('course') . "</b>: " . get_course($courseid)->fullname . "<br/>";
	$durationandcosts = block_addusers_get_course_duration_and_costs($courseid);
	echo '<b>' . get_string('cost') . "</b>: " . money_format('%i', $durationandcosts->costs) . "<br/>";
	echo '<b>' . get_string('courseduration', 'block_addusers') . "</b>: " . $durationandcosts->days . ' ' . get_string('days') . " <br/>";
	echo "<div class='block_adduser_warning'>" . get_string('warningcosts', 'block_addusers', $durationandcosts->costs) . "</div>";
	
	echo $form->display () . "<br/><hr/>";
}
else if ($submitted > 0) {
	$data = $form->get_data ();
	try{
		block_addusers_enroluser($data->userid, $data->courseid, $data->practicaldate, $USER->profile['Opleidernaam'], $USER->id);
		
		echo $OUTPUT->container_start ( 'block_adduser_nextstep' );
	
		$params = array('userid'=> $userid);
		$link = new moodle_url('/blocks/addusers/view_enrollments.php', $params);
		
		echo get_string('succesfully_added', 'block_addusers') . "<br/>";
		echo HTML_WRITER::link($link, get_string('enroll_student_more', 'block_addusers'));
		echo $OUTPUT->container_end ();
	} catch ( Exception $e ) {
		
		echo $OUTPUT->container_start ( 'block_adduser_error' );
		echo $e->getMessage ();
		
		echo $OUTPUT->container_end ();
	}
} else {
 	$userenrollments = block_addusers_get_enrolled_courses ( $user->id );
	
	echo $OUTPUT->container_start ( 'block_listpossibleenrollments' );
	$courses = block_addusers_get_eligable_courses ( $USER->id );
	
	// Remove courses already enrolled into
	$eligiblecourses = array ();
	foreach ( $courses as $entry ) {
		$found = false;
		foreach ( $userenrollments as $registered ) {
			if ($entry->id == $registered->id) {
				$found = true;
				break;
			}
		}
		if (! $found) {
			$eligiblecourses [] = $entry;
		}
	}
	
	echo '<h2>' . get_string ( 'eligible_courses', 'block_addusers' ) . '</h2>';
	block_addusers_list_possible_enrollments ( $eligiblecourses );
	
	echo $OUTPUT->container_end ();
	echo '<hr />';
	
	echo '<h2>' . get_string ( 'list_courses', 'block_addusers' , $user->firstname . ' ' . $user->lastname) . '</h2>';
	block_addusers_list_enrollments_table ( $userenrollments );
}
echo $OUTPUT->container_end ();
/**
 * Generate and show the table of user enrollments
 * 
 * @param array $userenrollments        	
 */
function block_addusers_list_enrollments_table($userenrollments) {
	global $PAGE, $CFG, $OUTPUT;
	
	// Setup table.
	$table = new flexible_table ( 'block-buyusers-enrollments-overview' );
	$tablecolumns = array (
			'coursename',
			'from',
			'to' 
	);
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
			get_string ( 'course' ),
			get_string ( 'from' ),
			get_string ( 'to' ) 
	);
	
	$table->define_headers ( $tableheaders );
	$table->sortable ( true );
	
	$table->set_attribute ( 'class', 'overviewTable' );
	$table->column_style_all ( 'padding', '5px' );
	$table->column_style_all ( 'text-align', 'left' );
	$table->column_style_all ( 'vertical-align', 'middle' );
	$table->column_style ( 'from', 'width', '30%' );
	$table->column_style ( 'to', 'width', '30%' );
	
	$table->define_baseurl ( $PAGE->url );
	$table->setup ();
	
	$tablerows = array ();
	foreach ( $userenrollments as $enrollment ) {
		$enrollmentfrom = ($enrollment->timestart == 0) ? '-' : userdate ( $enrollment->timestart, '%d-%m-%Y' );
		$enrollmentto = ($enrollment->timeend == 0) ? '-' : userdate ( $enrollment->timeend - 1, '%d-%m-%Y' );
		
		$table->add_data ( array (
				$enrollment->fullname,
				$enrollmentfrom,
				$enrollmentto 
		) );
	}
	
	$table->print_html ();
}

function block_addusers_list_possible_enrollments($courses) {
	global $PAGE, $CFG, $OUTPUT, $userid;
	
	// Setup table.
	$table = new flexible_table ( 'block-buyusers-possible-enrollments-overview' );
	$tablecolumns = array (
			'coursename',
			'cost',
			'days',
			'enroll' 
	);
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
			get_string ( 'course' ),
			get_string ( 'cost' ),
			ucfirst ( get_string ( 'days' ) ),
			get_string ( 'enroll', 'block_addusers' ) 
	);
	
	$table->define_headers ( $tableheaders );
	$table->sortable ( true );
	
	$table->set_attribute ( 'class', 'overviewTable' );
	$table->column_style_all ( 'padding', '5px' );
	$table->column_style_all ( 'text-align', 'left' );
	$table->column_style_all ( 'vertical-align', 'middle' );
	$table->column_style ( 'course', 'width', '30%' );
	$table->column_style ( 'cost', 'width', '30%' );
	$table->column_style ( 'days', 'width', '30%' );
	
	$table->define_baseurl ( $PAGE->url );
	$table->setup ();
	
	$tablerows = array ();
	foreach ( $courses as $course ) {
		$parameters = array (
				'showform' => 1,
				'courseid' => $course->id,
				'userid' => $userid
		);
		$link = new moodle_url ( '/blocks/addusers/view_enrollments.php', $parameters );
		$enrolllink = HTML_WRITER::link ( $link, get_string ( 'enroll', 'block_addusers' ) );
		
		$table->add_data ( array (
				$course->fullname,
				$course->costs,
				$course->days,
				$enrolllink 
		) );
	}
	
	$table->print_html ();
}

echo $OUTPUT->footer ();