<?php


// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/addusers/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/formslib.php');

class teacheroverviewform extends moodleform
{
	function definition() {
		global $CFG;

		$mform = $this->_form;
		
		$mform->addElement('text', 'teachername', get_string('fullname'));
		$mform->setDefault('teachername', $this->_customdata['teachername']);
		$mform->setType('teachername', PARAM_TEXT);
		
		$mform->addElement('text', 'credits', get_string('credits_to_add', 'block_addusers'));
		$mform->setType('credits', PARAM_FLOAT);
		
		$mform->addElement('text', 'comment', get_string('comment', 'block_addusers'));
		$mform->setType('comment', PARAM_TEXT);
		
		$mform->addElement('submit', 'submitbutton', get_string('add'));
		
		$mform->setType('teacherid', PARAM_NUMBER);
		$mform->addElement('hidden', 'teacherid', $this->_customdata['teacherid']);
		
		$mform->setType('submitted', PARAM_RAW);
		$mform->addElement('hidden', 'submitted', 1);
	}
}

// Gather form data.
$userid = $USER->id;
$submitted = optional_param ( 'submitted', 0, PARAM_INT );
$teacherid = optional_param ( 'teacherid', 0, PARAM_INT );
$credits = optional_param ( 'credits', 0, PARAM_INT );
$teachername = optional_param('teachername', '', PARAM_TEXT );

$PAGE->set_url ( '/blocks/addusers/usercredits.php', array () );
$PAGE->requires->css ( '/blocks/addusers/styles.css' );
$PAGE->set_context ( context_system::instance () );
$title = get_string ( 'user_credits', 'block_addusers' );
$PAGE->set_title ( $title );
$PAGE->set_heading ( $title );
$PAGE->navbar->add ( $title );
$PAGE->set_pagelayout ( 'standard' );

// Start page output.
echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title, 2 );
echo $OUTPUT->container_start ( 'block_listusers' );

$teacheroverviewform = new teacheroverviewform(null, array (
		'teacherid' => '', 'teachername' => '','credits' => ''
		) );

if($teacherid > 0)
{
	$teacheroverviewform = new teacheroverviewform ( null, array (
		'teacherid' => $teacherid, 'teachername' => $teachername,'credits' => $credits
	) );
	
	echo $teacheroverviewform->display ();
}

if ($submitted == 1) {
	$data = $teacheroverviewform->get_data ();
	block_addusers_add_credits($data->teacherid, $data->credits * 100, $data->comment);
	$teacheroverviewform = new teacheroverviewform ( null, array (
			'teacherid' => '$teacherid', 'teachername' => '$teachername','credits' => '$credits'
	) );
}

$teachers = block_addusers_get_teachers();

block_addusers_list_teachers_table ( $teachers );

echo $OUTPUT->container_end ();
echo $OUTPUT->footer();

function block_addusers_list_teachers_table($teachers) {
	global $PAGE, $CFG, $OUTPUT;
	
	// Setup table.
	$table = new flexible_table ( 'block-buyusers-users-overview' );
	
	$tablecolumns = array (
			'name',
			'institution',
			'credits',
			'details'
	);
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
			get_string ( 'fullname' ),
			get_string ( 'institution', 'block_addusers' ),
			get_string ( 'credits', 'block_addusers' ),
			get_string ( 'add' )
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
	foreach ($teachers as $teacher)
	{
		if(!isset($teacher->amount)){
			block_addusers_add_teacher($teacher->id);
			$teacher->amount = 0;
		}
			
		$params = array('teacherid' => $teacher->id, 'credits' => $teacher->amount, 'teachername' => $teacher->firstname . ' ' . $teacher->lastname);
		
		$link = new moodle_url('/blocks/addusers/usercredits.php', $params);
		$icon = $OUTPUT->pix_icon('add', 'details', 'block_addusers', array('class' => 'nowicon'));
				
		$details = HTML_WRITER::link($link, $icon);
		
		$table->add_data(array(
				$teacher->firstname . ' ' . $teacher->lastname, $teacher->data, '&#8364;' . money_format('%i', $teacher->amount / 100), $details)
		);
	}

	$table->print_html ();
}