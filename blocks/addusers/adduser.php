<?php
// Include required files.
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/addusers/lib.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/formslib.php');
class adduserform extends moodleform {
	function definition() {
		global $CFG;
		
		$mform = $this->_form;
		
		$mform->addElement ( 'text', 'firstname', get_string ( 'firstname' ) );
		$mform->setType ( 'firstname', PARAM_TEXT );
		$mform->addElement ( 'text', 'lastname', get_string ( 'lastname' ) );
		$mform->setType ( 'lastname', PARAM_TEXT );
		$mform->addElement ( 'text', 'email', get_string ( 'email' ) );
		$mform->setType ( 'email', PARAM_EMAIL );
		$mform->addElement ( 'submit', 'submitbutton', get_string ( 'add' ) );
		
		$mform->setType ( 'userid', PARAM_RAW );
		$mform->addElement ( 'hidden', 'userid', $this->_customdata ['userid'] );
		
		$mform->setType ( 'submitted', PARAM_RAW );
		$mform->addElement ( 'hidden', 'submitted', 1 );
	}
}

// Gather form data.
$userid = $USER->id;
$submitted = optional_param ( 'submitted', 0, PARAM_INT );

$PAGE->set_url ( '/blocks/addusers/adduser.php', array () );
$PAGE->requires->css ( '/blocks/addusers/styles.css' );
$PAGE->set_context ( context_system::instance () );
$title = get_string ( 'add_user', 'block_addusers' );
$PAGE->set_title ( $title );
$PAGE->set_heading ( $title );
$PAGE->navbar->add ( $title );
$PAGE->set_pagelayout ( 'standard' );
$sort = '';

// Start page output.
echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title, 2 );
echo $OUTPUT->container_start ( 'block_adduser' );

$userform = new adduserform ( null, array (
		'userid' => $userid 
) );

if ($submitted == 1) {
	$data = $userform->get_data ();
	
	try {
		$returnobject = block_addusers_createuser ( $data->email, $data->firstname, $data->lastname, $data->email, $data->userid );
		echo $OUTPUT->container_start ( 'block_adduser_success' );
		$user = new stdClass();
		$user->username = $data->email;
		$user->password = $returnobject->message;
		echo get_string ( 'password', 'block_addusers', $user);
		
		echo $OUTPUT->container_end ();
		echo $OUTPUT->container_start ( 'block_adduser_nextstep' );
	
		$params = array('userid'=> $returnobject->userid);
		$link = new moodle_url('/blocks/addusers/view_enrollments.php', $params);
		
		echo HTML_WRITER::link($link, get_string('enroll_student', 'block_addusers'));
		echo $OUTPUT->container_end ();
	} catch ( Exception $e ) {
		echo $OUTPUT->container_start ( 'block_adduser_error' );
		echo $e->getMessage ();
		
		echo $OUTPUT->container_end ();
		echo $userform->display ();
	}
}else {
	echo $userform->display ();
}
echo $OUTPUT->container_end ();
echo $OUTPUT->footer();