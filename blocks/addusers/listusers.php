<?php


// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/addusers/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/formslib.php');

define ( 'DEFAULT_PAGE_SIZE', 20 );
define ( 'SHOW_ALL_PAGE_SIZE', 5000 );


class useroverviewform extends moodleform
{
	function definition() {
		global $CFG;

		$mform = $this->_form;
		$mform->addElement('text', 'name', get_string('name'));
		$mform->setType('name', PARAM_TEXT);
		
		$mform->addElement('submit', 'submitbutton', get_string('show'));
		
		$mform->setType('page', PARAM_RAW);
		$mform->addElement('hidden', 'page', $this->_customdata['page']);
		
		$mform->setType('perpage', PARAM_RAW);
		$mform->addElement('hidden', 'perpage', $this->_customdata['perpage']);
				
		$mform->setType('submitted', PARAM_RAW);
		$mform->addElement('hidden', 'submitted', 1);
	}
}

// Gather form data.
$userid = $USER->id;
$submitted = optional_param ( 'submitted', 0, PARAM_INT );
$page     = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage  = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page

$PAGE->set_url ( '/blocks/addusers/listusers.php', array () );
$PAGE->requires->css ( '/blocks/addusers/styles.css' );
$PAGE->set_context ( context_system::instance () );
$title = get_string ( 'list_users', 'block_addusers' );
$PAGE->set_title ( $title );
$PAGE->set_heading ( $title );
$PAGE->navbar->add ( $title );
$PAGE->set_pagelayout ( 'standard' );



// Start page output.
echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title, 2 );
echo $OUTPUT->container_start ( 'block_listusers' );

$userform = new useroverviewform ( null, array (
		'userid' => $userid, 'page' => $page, 'perpage' => $perpage
) );

$users = array();

$groupid = block_addusers_get_groupid($USER->profile['Opleidernaam']);
if ($submitted == 1) {
	$data = $userform->get_data ();
	
	$users = block_addusers_get_users($groupid, $data->name);
} else {
	$users = block_addusers_get_users($groupid);
}

echo $userform->display ();


block_addusers_list_users_table ( $users );

echo $OUTPUT->container_end ();
echo $OUTPUT->footer();

function block_addusers_list_users_table($users) {
	global $PAGE, $page, $CFG, $perpage, $OUTPUT;
	$numberofentries = count ( $users );
	$paged = $numberofentries > $perpage;

	if (! $paged) {
		$page = 0;
	}

	// Setup table.
	$table = new flexible_table ( 'block-buyusers-users-overview' );
	$table->pagesize ( $perpage, $numberofentries );
	$tablecolumns = array (
			'firstname',
			'lastname',
			'email',
			'enrollments'
	);
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
			get_string ( 'firstname' ),
			get_string ( 'lastname' ),
			get_string ( 'email' ),
			get_string ( 'enrollments', 'block_addusers' )
	);
	$table->define_headers ( $tableheaders );
	$table->sortable ( true );

	$table->set_attribute ( 'class', 'overviewTable' );
	$table->column_style_all ( 'padding', '5px' );
	$table->column_style_all ( 'text-align', 'left' );
	$table->column_style_all ( 'vertical-align', 'middle' );
	$table->column_style ( 'firstname', 'width', '30%' );
	$table->column_style ( 'lastname', 'width', '30%' );
	$table->column_style ( 'email', 'width', '30%' );
	$table->column_style ( 'enrollments', 'width', '10%' );
	
	$table->define_baseurl($PAGE->url);
	$table->setup();

	// Get range of rows for page.
	$start = $page * $perpage;
	$end = ($start + $perpage > $numberofentries) ? $numberofentries : ($start + $perpage);

	
	
	$tablerows = array();
	foreach ($users as $user)
	{
		$enrollmentsparameters = array('userid' => $user->id);
		$enrollmentslink = new moodle_url('/blocks/addusers/view_enrollments.php', $enrollmentsparameters);
		$enrollmentdetailsicon = $OUTPUT->pix_icon('enroll', 'details', 'block_addusers', array('class' => 'nowicon'));
		
		$details = HTML_WRITER::link($enrollmentslink, $enrollmentdetailsicon);
		
		$table->add_data(array(
				$user->firstname, $user->lastname, $user->email, $details)
		);
	}

	$table->print_html ();

	$perpageurl = clone($PAGE->url);

	if ($paged) {
		$perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
		echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $numberofentries)), array(), 'showall');
	} else if ($numberofentries > DEFAULT_PAGE_SIZE) {
		$perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
		echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');
	}
}