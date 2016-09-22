<?php

// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/ccv/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/formslib.php');

class groupform extends moodleform
{
	function definition() {
		global $CFG;
		
		$mform = $this->_form;
		$mform->disable_form_change_checker();
		
		$mform->addElement('select', 'group', get_string('group', 'block_ccv'), $this->_customdata['options']);
		$mform->setType('group', PARAM_TEXT);
		
		$mform->addElement('select', 'course', get_string('course'), $this->_customdata['courseoptions']);
		$mform->setType('course', PARAM_TEXT);
		
		$mform->addElement('date_selector', 'fromdate', get_string('from'), array('startyear' => 2010, 'stopyear'  => 2025,
				'optional'  => false));
		
		$mform->addElement('date_selector', 'todate', get_string('to'), array('startyear' => 2010, 'stopyear'  => 2025,
				'optional'  => false));
		
		$mform->addElement('select', 'report', get_string('report'), $this->_customdata['reportoptions']);
		$mform->setType('report', PARAM_TEXT);
		
		$mform->addElement('advcheckbox', 'addtime', get_string('yes'));
		
		$mform->setType('submitted', PARAM_RAW);
		$mform->addElement('hidden', 'submitted', 1);
		
		$mform->addElement('select', 'sort', get_string('sort'), array('lastname'=>'Achternaam','time'=>'Tijd','test'=>'test'));
		$mform->setType('sort', PARAM_TEXT);
		
		$mform->addElement ( 'submit', 'submitbutton', get_string ( 'submit' ) );
	}
}

$groupsubmitted 	= optional_param('submitted', 0, PARAM_INT);
$PAGE->requires->css('/blocks/ccv/styles.css');
$PAGE->set_url('/blocks/ccv/overview.php');
$PAGE->set_context(context_system::instance());

$title = get_string('overview', 'block_ccv');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->set_pagelayout('report');
$sort = '';

// Start page output.
echo $OUTPUT->header();
echo $OUTPUT->heading($title, 2);
echo $OUTPUT->container_start('block_progress');

$groups = block_ccv_get_groups();
$groupnames = array();
$groupnames[] = get_string('group', 'block_ccv');

foreach ($groups as $group)
{
	$groupnames[$group->groupid] = $group->groupname;
}

$courses = block_ccv_get_courses();
$coursenames = array();
$coursenames[] = get_string('course');
foreach ($courses as $course)
{
	$coursenames[$course->id] = $course->fullname;
}

$reporttypes = array('h'=>'8.2.5.h','a'=>'8.2.6.a','b'=>'8.2.6.b','c'=>'8.2.6.c','d'=>'8.2.6.d');

$groupform = new groupform(null, array('options' => $groupnames, 'courseoptions' =>$coursenames, 'reportoptions'=>$reporttypes));
echo $groupform->display();

if($groupsubmitted)
{
	$data = $groupform->get_data();
	
	$groupid = $data->group;
	$courseid = $data->course;
	$report = $data->report;
	$fromdate = $data->fromdate;
	$todate = $data->todate;
	$addtime = $data->addtime;
	$sort = $data->sort;
	
	$parameters = array('sort'=>$sort, 'addtime' => $addtime, 'courseid' => $courseid, 'groupid' => $groupid, 'fromdate'=> $fromdate, 'todate'=>$todate);
	
	switch ($report) {
		case 'a':
			$url = new moodle_url('/blocks/ccv/generate_report_a.php', $parameters);
			break;
		case 'b':
			$url = new moodle_url('/blocks/ccv/generate_report_b.php', $parameters);
			break;
		case 'c':
			$url = new moodle_url('/blocks/ccv/generate_report_c.php', $parameters);
			break;
		case 'd':
			$url = new moodle_url('/blocks/ccv/generate_report_d.php', $parameters);
			break;
		case 'h':
			$url = new moodle_url('/blocks/ccv/generate_report_8.2.5.h.php', $parameters);
			break;
	}
	
	$label = get_string('download');
	$options = array('class' => 'overviewButton');
			
	echo $OUTPUT->single_button($url, $label, 'post', $options);
} 

echo $OUTPUT->container_end();
echo $OUTPUT->footer();