<?php


// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/addusers/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/formslib.php');

$PAGE->set_url ( '/blocks/addusers/usercredits.php', array () );
require_login ( '', false );
require_capability ( 'block/addusers:addcredits', context_system::instance ());

class teacheroverviewform extends moodleform
{
	function definition() {
		global $CFG;

		$mform = $this->_form;
		
		$mform->addElement('text', 'groupname', get_string('institution', 'block_addusers'));
		$mform->setDefault('groupname', $this->_customdata['groupname']);
		$mform->setType('groupname', PARAM_RAW);
		
		$mform->addElement('text', 'credits', get_string('credits_to_add', 'block_addusers'));
		$mform->setType('credits', PARAM_FLOAT);
		
		$mform->addElement('text', 'comment', get_string('comment', 'block_addusers'));
		$mform->setType('comment', PARAM_TEXT);
		
		$mform->addElement('submit', 'submitbutton', get_string('add'));
		
		$mform->setType ( 'groupid', PARAM_RAW );
		$mform->addElement ( 'hidden', 'groupid', $this->_customdata ['groupid'] );
		
		$mform->setType('submitted', PARAM_RAW);
		$mform->addElement('hidden', 'submitted', 1);
	}
}

// Gather form data.
$userid = $USER->id;
$submitted = optional_param ( 'submitted', 0, PARAM_INT );
$groupname = optional_param ( 'groupname', '', PARAM_RAW );
$credits = optional_param ( 'credits', 0, PARAM_INT );
$groupid = optional_param ( 'groupid', 0, PARAM_INT );


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
		'groupname' => '', 'credits' => '', 'groupid' => $groupid
		) );

if($groupname != '')
{
	$teacheroverviewform = new teacheroverviewform ( null, array (
		'groupname' => $groupname, 'credits' => $credits, 'groupid' => $groupid
	) );
	if($submitted <> 1)
		echo $teacheroverviewform->display ();
}

if ($submitted == 1) {
	$data = $teacheroverviewform->get_data ();
	block_addusers_add_credits($data->groupid, $data->credits * 100, $data->comment);
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
			'institution',
			'credits',
			'details'
	);
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
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
			echo print_r($teacher);
			block_addusers_add_group($teacher->groupname);
			$teacher->amount = 0;
		}
		$params = array('credits' => $teacher->amount, 'groupname' => $teacher->groupname, 'groupid' => $teacher->groupid);
		
		$link = new moodle_url('/blocks/addusers/usercredits.php', $params);
		$icon = $OUTPUT->pix_icon('add', 'details', 'block_addusers', array('class' => 'nowicon'));
				
		$details = HTML_WRITER::link($link, $icon);
		
		$table->add_data(array(
				$teacher->groupname, '&#8364;' . money_format('%i', $teacher->amount / 100), $details)
		);
	}

	$table->print_html ();
}