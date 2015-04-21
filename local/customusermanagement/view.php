<?php

$cmid = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('customusermanagement', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
 
require_login($course, true, $cm);
$PAGE->set_url('/mod/customusermanagement/view.php', array('id' => $cm->id));
$PAGE->set_title('Custom User Management');
$PAGE->set_heading('Heading');