<?php


// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/addusers/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/formslib.php');

class courseoverviewform extends moodleform
{
	function definition() {
		global $CFG;

		$mform = $this->_form;
		
		$mform->addElement('text', 'coursename', get_string('course'));
		$mform->setDefault('coursename', $this->_customdata['coursename']);
		$mform->setType('coursename', PARAM_TEXT);
		
		$mform->addElement('text', 'costs', get_string('cost', 'block_addusers'));
		$mform->setDefault('costs', $this->_customdata['costs']);
		$mform->setType('costs', PARAM_FLOAT);
		
		$mform->addElement('text', 'days', get_string('days'));
		$mform->setDefault('days', $this->_customdata['days']);
		$mform->setType('days', PARAM_NUMBER);
		
		$mform->addElement('submit', 'submitbutton', get_string('add'));
		
		$mform->setType('courseid', PARAM_RAW);
		$mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
		
		$mform->setType('brid', PARAM_RAW);
		$mform->addElement('hidden', 'brid', $this->_customdata['brid']);
		
		$mform->setType('submitted', PARAM_RAW);
		$mform->addElement('hidden', 'submitted', 1);
	}
}

// Gather form data.
$userid = $USER->id;
$submitted = optional_param ( 'submitted', 0, PARAM_INT );
$courseid = optional_param ( 'courseid', -1, PARAM_INT );

$PAGE->set_url ( '/blocks/addusers/coursecosts.php', array () );
$PAGE->requires->css ( '/blocks/addusers/styles.css' );
$PAGE->set_context ( context_system::instance () );
$title = get_string ( 'course_costs', 'block_addusers' );
$PAGE->set_title ( $title );
$PAGE->set_heading ( $title );
$PAGE->navbar->add ( $title );
$PAGE->set_pagelayout ( 'standard' );



// Start page output.
echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title, 2 );
echo $OUTPUT->container_start ( 'block_listusers' );

$courseform = new courseoverviewform(null, array (
		'coursename' => '', 'courseid' => '','brid' => '', 'costs' => '', 'days' => ''
		) );

if($courseid > 0)
{
	$course = block_addusers_get_course_and_price($courseid);
	
	$courseform = new courseoverviewform ( null, array (
			'coursename' => $course->shortname, 'courseid' => $course->id,'brid' => $course->brid, 'costs' => $course->costs, 'days' => $course->days
	) );
	
	echo $courseform->display ();
}

if ($submitted == 1) {
	$data = $courseform->get_data ();
	block_addusers_save_price($data->courseid, $data->costs, $data->brid, $data->days);
	$courseform = new courseoverviewform ( null, array (
			'coursename' => $data->coursename, 'courseid' => $data->courseid,'brid' => $data->brid, 'costs' => $data->costs, 'days' => $data->days
	) );
}

$courses = block_addusers_get_courses();

block_addusers_list_courses_table ( $courses );

echo $OUTPUT->container_end ();

echo $OUTPUT->footer();

function block_addusers_list_courses_table($courses) {
	global $PAGE, $CFG, $OUTPUT;
	
	// Setup table.
	$table = new flexible_table ( 'block-buyusers-users-overview' );
	
	$tablecolumns = array (
			'courseid',
			'coursename',
			'courseshortname',
			'costs',
			'days',
			'edit'
	);
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
			'#',
			get_string ( 'course' ),
			get_string ( 'shortname' ),
			get_string ( 'cost', 'block_addusers' ),
			get_string ( 'days' ),
			get_string ( 'edit' )
	);
	$table->define_headers ( $tableheaders );
	$table->sortable ( true );

	$table->set_attribute ( 'class', 'overviewTable' );
	$table->column_style_all ( 'padding', '5px' );
	$table->column_style_all ( 'text-align', 'left' );
	$table->column_style_all ( 'vertical-align', 'middle' );
	
	
	$table->define_baseurl($PAGE->url);
	$table->setup();

	
	$tablerows = array();
	foreach ($courses as $course)
	{
		$params = array('courseid' => $course->id, 'brid' => $course->brid, 'costs' => $course->costs, 'days' => $course->days);
		
		$link = new moodle_url('/blocks/addusers/coursecosts.php', $params);
		$icon = $OUTPUT->pix_icon('edit', 'details', 'block_addusers', array('class' => 'nowicon'));
				
		$details = HTML_WRITER::link($link, $icon);
		
		$table->add_data(array(
				$course->id, $course->fullname, $course->shortname, $course->costs, $course->days, $details)
		);
	}

	$table->print_html ();
}