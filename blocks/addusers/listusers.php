<?php

// Include required files.
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/addusers/lib.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/formslib.php');

require_once ($CFG->dirroot . '/user/filters/lib.php');
require_once ($CFG->dirroot . '/user/lib.php');

define ( 'DEFAULT_PAGE_SIZE', 15 );
define ( 'SHOW_ALL_PAGE_SIZE', 5000 );

$PAGE->set_url ( '/blocks/addusers/listusers.php', array () );
require_login ( '', false );

class useroverviewform extends moodleform {
	function definition() {
		global $CFG;
		
		$mform = $this->_form;
		$mform->addElement ( 'text', 'name', get_string ( 'name' ) );
		$mform->setType ( 'name', PARAM_TEXT );
		
		$mform->addElement ( 'submit', 'submitbutton', get_string ( 'show' ) );
		
		$mform->setType ( 'page', PARAM_RAW );
		$mform->addElement ( 'hidden', 'page', $this->_customdata ['page'] );
		
		$mform->setType ( 'perpage', PARAM_RAW );
		$mform->addElement ( 'hidden', 'perpage', $this->_customdata ['perpage'] );
		
		$mform->setType ( 'submitted', PARAM_RAW );
		$mform->addElement ( 'hidden', 'submitted', 1 );
	}
}

class edituserform extends moodleform {
	function definition() {
		global $CFG;

		$mform = $this->_form;
		$mform->setDisableShortforms(true);
		
		$mform->addElement('header', 'changepassword', get_string('edituser'), '');
		
		$mform->addElement('static', 'username', get_string('username'), $this->_customdata ['username']);
		
		$mform->addElement ( 'text', 'firstname', get_string ( 'firstname' ) );
		$mform->addRule('firstname', get_string('required'), 'required', null, 'client');
		$mform->setType ( 'firstname', PARAM_TEXT );
		$mform->setDefault( 'firstname', $this->_customdata ['firstname']);
		
		$mform->addElement ( 'text', 'lastname', get_string ( 'lastname' ) );
		$mform->addRule('lastname', get_string('required'), 'required', null, 'client');
		$mform->setType ( 'lastname', PARAM_TEXT );
		$mform->setDefault( 'lastname', $this->_customdata ['lastname']);
		
		$mform->addElement ( 'text', 'email', get_string ( 'email' ) );
		$mform->addRule('email', get_string('required'), 'required', null, 'client');
		$mform->setType ( 'email', PARAM_EMAIL );
		$mform->setDefault( 'email', $this->_customdata ['email']);
		
		$mform->addElement ( 'text', 'password', get_string ( 'password' ) );
		$mform->setType ( 'password', PARAM_TEXT );
				
		$mform->addElement ( 'submit', 'submitbutton', get_string ( 'edit' ) );

		$mform->setType ( 'page', PARAM_RAW );
		$mform->addElement ( 'hidden', 'page', $this->_customdata ['page'] );
	
		$mform->setType ( 'userid', PARAM_RAW );
		$mform->addElement ( 'hidden', 'userid', $this->_customdata ['userid'] );
		
		$mform->setType ( 'perpage', PARAM_RAW );
		$mform->addElement ( 'hidden', 'perpage', $this->_customdata ['perpage'] );
	
		$mform->setType ( 'editsubmitted', PARAM_RAW );
		$mform->addElement ( 'hidden', 'editsubmitted', 1 );
		
		$mform->setType ( 'oldemail', PARAM_EMAIL );
		$mform->addElement ( 'hidden', 'oldemail', 1 );
		$mform->setDefault( 'oldemail', $this->_customdata ['email']);
	}
	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		
		if(block_addusers_checkuseremail($data['email']) and strcmp($data['email'], $data['oldemail']) !== 0)
		{
			$errors['email'] = get_string('email_taken', 'block_addusers');
		}
		
		if (!$data['password'])
			return $errors;
		
		if (user_is_previously_used_password($data['userid'], $data['password'])) {
			$errors['password'] = get_string('errorpasswordreused', 'core_auth');
		}
				
		if (!check_password_policy($data['password'], $errmsg)) {
			$errors['password'] = $errmsg;
		}
		
		return $errors;
	}
}


// Gather form data.
$userid 		= 	$USER->id;
$submitted 		= 	optional_param ( 'submitted', 0, PARAM_INT );
$page 			= 	optional_param ( 'page', 0, PARAM_INT ); // Which page to show.
$perpage 		= 	optional_param ( 'perpage', DEFAULT_PAGE_SIZE, PARAM_INT ); // How many per page
$edit			= 	optional_param('edit', 0, PARAM_INT);
$selecteduserid	= 	optional_param('userid', 0, PARAM_INT);
$editsubmitted	= 	optional_param('editsubmitted', 0, PARAM_INT);

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

$groupid = block_addusers_get_groupid ( $USER->profile ['Opleidernaam'] );
$users = block_addusers_get_users ( $groupid);
$error = false;

if($edit || $editsubmitted)
{
	$user = block_addusers_get_user_details($selecteduserid);
	
	$editform = new edituserform();
	$editform->set_data(array (
			'firstname' => $user->firstname,
			'lastname' => $user->lastname,
			'email' => $user->email,
			'username' => $user->username,
			'userid' => $selecteduserid,
			'page' => $page,
			'perpage' => $perpage
	) );
	if($editsubmitted)
	{
		$data = $editform->get_data ();
	
		if(!$data)
		{
			echo $editform->display ();
			$error = true;
		} else {
	
		$user = block_addusers_get_user_details($data->userid);
	
		$oldemail = $user->email;
		$user->firstname = $data->firstname;
		$user->lastname = $data->lastname;
		$user->email = $data->email;
	
		block_addusers_update_user_details($user);
	
		$password = $data->password;
		if($password)
			if(!block_addusers_update_password($user, $password))
			{
				echo $OUTPUT->container_start ( 'block_adduser_error' );
				echo get_string('unknownerror');
				
				echo $OUTPUT->container_end ();
				echo $editform->display ();
				$error = true;
			}
	
			$users = block_addusers_get_users ($groupid);
		}
	}
	if(!$editsubmitted)
		echo $editform->display ();
} 



$userform = new useroverviewform ( 
		null, array (
			'userid' => $userid,
			'page' => $page,
			'perpage' => $perpage 
	) 
);

if(!$edit && !$error)
	echo $userform->display ();

if ($submitted == 1) {
	$data = $userform->get_data ();
	$users = block_addusers_get_users ( $groupid, $data->name );
}

block_addusers_list_users_table ( $users );

echo $OUTPUT->container_end ();
echo $OUTPUT->footer ();

function block_addusers_list_users_table($users) {
	global $PAGE, $page, $CFG, $perpage, $OUTPUT;
	
	$ufiltering = new user_filtering ();
	
	$numberofentries = count ( $users );
	$paged = $numberofentries > $perpage;
	
	if (! $paged) {
		$page = 0;
	}
	
	// Setup table.
	$table = new flexible_table ( 'block-buyusers-users-overview' );
	$table->pagesize ( $perpage, $numberofentries );
	$tablecolumns = array (
			'username',
			'firstname',
			'lastname',
			'email',
			'edit',
			'enrollments' 
	);
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
			get_string ( 'username' ),
			get_string ( 'firstname' ),
			get_string ( 'lastname' ),
			get_string ( 'email' ),
			get_string ( 'edit' ),
			get_string ( 'enrollments', 'block_addusers' ) 
	);
	$table->define_headers ( $tableheaders );
	$table->sortable ( true );
	
	$table->set_attribute ( 'class', 'overviewTable' );
	$table->column_style_all ( 'padding', '5px' );
	$table->column_style_all ( 'text-align', 'left' );
	$table->column_style_all ( 'vertical-align', 'middle' );
	$table->column_style ( 'username', 'width', '25%' );
	$table->column_style ( 'firstname', 'width', '20%' );
	$table->column_style ( 'lastname', 'width', '20%' );
	$table->column_style ( 'email', 'width', '25%' );
	$table->column_style ( 'edit', 'width', '5%' );
	$table->column_style ( 'enrollments', 'width', '5%' );
	
	$table->define_baseurl ( $PAGE->url );
	$table->setup ();
	
	// Get range of rows for page.
	$start = $page * $perpage;
	$end = ($start + $perpage > $numberofentries) ? $numberofentries : ($start + $perpage);
	
	global $sort;
	$sort = $table->get_sql_sort ();
	$nosort = strncmp ( $sort, 'enrollme', 8 ) == 0;
	if (! $nosort)
		$nosort = strncmp ( $sort, 'edit', 4 ) == 0;
	
	if (! $sort || ($paged && $nosort)) {
		$sort = 'lastname DESC';
	}
	usort ( $users, 'block_addusers_listusers_compare_rows' );
	
	// Get range of students for page.
	$startuser = $page * $perpage;
	$numberofusers = count ( $users );
	$enduser = ($startuser + $perpage > $numberofusers) ? $numberofusers : ($startuser + $perpage);
	
	$tablerows = array ();
	
	for($i = $startuser; $i < $enduser; $i ++) {
		$user = $users [$i];
		
		// Edit
		$editparameters = array (
				'userid' => $user->id,
				'edit' => '1'
		);
		$editlink = new moodle_url ( '/blocks/addusers/listusers.php', $editparameters );
		$editicon = $OUTPUT->pix_icon ( 'edit', 'details', 'block_addusers', array (
				'class' => 'editicon' 
		) );
		
		$edit = HTML_WRITER::link ( $editlink, $editicon );
		
		// Enrollments
		$enrollmentsparameters = array (
				'userid' => $user->id 
		);
		$enrollmentslink = new moodle_url ( '/blocks/addusers/view_enrollments.php', $enrollmentsparameters );
		$enrollmentdetailsicon = $OUTPUT->pix_icon ( 'enroll', 'details', 'block_addusers', array (
				'class' => 'nowicon' 
		) );
		
		$details = HTML_WRITER::link ( $enrollmentslink, $enrollmentdetailsicon );
		
		$table->add_data ( array (
				$user->username,
				$user->firstname,
				$user->lastname,
				$user->email,
				$edit,
				$details 
		) );
	}
	
	$table->print_html ();
	
	$perpageurl = clone ($PAGE->url);
	
	if ($paged) {
		$perpageurl->param ( 'perpage', SHOW_ALL_PAGE_SIZE );
		echo $OUTPUT->container ( html_writer::link ( $perpageurl, get_string ( 'showall', '', $numberofentries ) ), array (), 'showall' );
	} else if ($numberofentries > DEFAULT_PAGE_SIZE) {
		$perpageurl->param ( 'perpage', DEFAULT_PAGE_SIZE );
		echo $OUTPUT->container ( html_writer::link ( $perpageurl, get_string ( 'showperpage', '', DEFAULT_PAGE_SIZE ) ), array (), 'showall' );
	}
}

/**
 * Compares two table row elements for ordering.
 *
 * @param mixed $a
 *        	element containing name, online time and progress info
 * @param mixed $b
 *        	element containing name, online time and progress info
 * @return order of pair expressed as -1, 0, or 1
 *        
 */
function block_addusers_listusers_compare_rows($a, $b) {
	global $sort;
	// Process each of the one or two orders.
	$orders = explode ( ',', $sort );
	
	foreach ( $orders as $order ) {
		
		// Extract the order information.
		$orderelements = explode ( ' ', trim ( $order ) );
		$aspect = $orderelements [0];
		$ascdesc = $orderelements [1];
		
		// Compensate for presented vs actual.
		switch ($aspect) {
			case 'username' :
				$aspect = 'username';
				break;
			case 'firstname' :
				$aspect = 'firstname';
				break;
			case 'lastname' :
				$aspect = 'lastname';
				break;
			case 'email' :
				$aspect = 'email';
				break;
			case 'enrollments' :
				$aspect = 'lastname';
				break;
			case 'edit' :
				$aspect = 'lastname';
				break;
		}
		
		// Check of order can be established.
		if (is_array ( $a )) {
			$first = $a [$aspect];
			$second = $b [$aspect];
		} else {
			$first = $a->$aspect;
			$second = $b->$aspect;
		}
		
		if (preg_match ( '/name/', $aspect )) {
			$first = strtolower ( $first );
			$second = strtolower ( $second );
		}
		
		if ($first < $second) {
			return $ascdesc == 'ASC' ? 1 : - 1;
		}
		if ($first > $second) {
			return $ascdesc == 'ASC' ? - 1 : 1;
		}
	}
	
	// If previous ordering fails, consider values equal.
	return 0;
}