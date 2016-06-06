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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Progress Bar block overview page
 *
 * @package    contrib
 * @subpackage block_progress
 * @copyright  2010 Michael de Raadt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include required files.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/progress/lib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/formslib.php');


class dateform extends moodleform
{
	function definition() {
		global $CFG;

		$mform = $this->_form;
	
		$mform->addElement('date_selector', 'activedate', get_string('active'), array('startyear' => 2010, 'stopyear'  => 2025,
    			'optional'  => false));
		$mform->setDefault('activedate', date('U', strtotime("-10 days")));
		$mform->addElement('submit', 'submitbutton', get_string('show'));
		
		$mform->setType('progressbarid', PARAM_RAW);
		$mform->addElement('hidden', 'progressbarid', $this->_customdata['progressbarid']);
		
		$mform->setType('courseid', PARAM_RAW);
		$mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
		
		$mform->setType('page', PARAM_RAW);
		$mform->addElement('hidden', 'page', $this->_customdata['page']);
		
		$mform->setType('perpage', PARAM_RAW);
		$mform->addElement('hidden', 'perpage', $this->_customdata['perpage']);
		
		$mform->setType('group', PARAM_RAW);
		$mform->addElement('hidden', 'group', $this->_customdata['group']);
		
		$mform->setType('active', PARAM_RAW);
		$mform->addElement('hidden', 'active', $this->_customdata['active']);
		
		$mform->setType('submitted', PARAM_RAW);
		$mform->addElement('hidden', 'submitted', 1);
	}
}

define('USER_SMALL_CLASS', 20);   // Below this is considered small.
define('USER_LARGE_CLASS', 200);  // Above this is considered large.
define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

// Gather form data.
$id       = required_param('progressbarid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$page     = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage  = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$group    = optional_param('group', 0, PARAM_INT); // Group selected.
$showinactive    = optional_param('active', 1, PARAM_INT);
$datesubmitted 	= optional_param('submitted', 0, PARAM_INT);

// Determine course and context.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = block_progress_get_course_context($courseid);

// Get specific block config and context.
$progressblock = $DB->get_record('block_instances', array('id' => $id), '*', MUST_EXIST);
$progressconfig = unserialize(base64_decode($progressblock->configdata));
$progressblockcontext = block_progress_get_block_context($id);

// Set up page parameters.
$PAGE->set_course($course);
$PAGE->requires->css('/blocks/progress/styles.css');
$PAGE->set_url(
    '/blocks/progress/overview.php',
    array(
        'progressbarid' => $id,
        'courseid' => $courseid,
        'page' => $page,
        'perpage' => $perpage,
        'group' => $group,
    	'active' => $showinactive,
    )
);

$PAGE->set_context($context);
$title = get_string('overview', 'block_progress');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->set_pagelayout('report');
$sort = '';

// Check user is logged in and capable of accessing the Overview.
require_login($course, false);
require_capability('block/progress:overview', $progressblockcontext);

// Start page output.
echo $OUTPUT->header();
echo $OUTPUT->heading($title, 2);
echo $OUTPUT->container_start('block_progress');

// Get the modules to check progress on.
$modules = block_progress_modules_in_use($course->id);
if (empty($modules)) {
    echo get_string('no_events_config_message', 'block_progress');
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
    die();
}

// Check if activities/resources have been selected in config.
$events = block_progress_event_information($progressconfig, $modules, $course->id);
if ($events == null) {
    echo get_string('no_events_message', 'block_progress');
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
    die();
}
if (empty($events)) {
    echo get_string('no_visible_events_message', 'block_progress').'&nbsp;';
    echo $OUTPUT->container_end();
    echo $OUTPUT->footer();
    die();
}
$numevents = count($events);

// Determine if a role has been selected.
$sql = "SELECT DISTINCT r.id, r.name, r.archetype
          FROM {role} r, {role_assignments} a
         WHERE a.contextid = :contextid
           AND r.id = a.roleid
           AND r.archetype = :archetype";
$params = array('contextid' => $context->id, 'archetype' => 'student');
$studentrole = $DB->get_record_sql($sql, $params);
if ($studentrole) {
    $studentroleid = $studentrole->id;
} else {
    $studentroleid = 0;
}
$roleselected = optional_param('role', $studentroleid, PARAM_INT);
$rolewhere = $roleselected != 0 ? "AND a.roleid = $roleselected" : '';

// Output group selector if there are groups in the course.
echo $OUTPUT->container_start('progressoverviewmenus');
$groupselected = 0;
$groupuserid = $USER->id;
if (has_capability('moodle/site:accessallgroups', $context)) {
    $groupuserid = 0;
}
$groupids = array();
$groups = groups_get_all_groups($course->id, $groupuserid);
if (!empty($groups)) {
    $groupstodisplay = array(0 => get_string('allparticipants'));
    foreach ($groups as $groupid => $groupobject) {
        $groupstodisplay[$groupid] = $groupobject->name;
        $groupids[] = $groupid;
    }
    if (!in_array($group, $groupids)) {
        $group = 0;
        $PAGE->url->param('group', $group);
    }
    echo get_string('groupsvisible').'&nbsp;';
    echo $OUTPUT->single_select($PAGE->url, 'group', $groupstodisplay, $group);   
}
echo $OUTPUT->container_end();

// Output the roles menu.
$sql = "SELECT DISTINCT r.id, r.name, r.shortname
          FROM {role} r, {role_assignments} a
         WHERE a.contextid = :contextid
           AND r.id = a.roleid";

$roles = role_fix_names($DB->get_records_sql($sql, $params), $context);
$rolestodisplay = array(0 => get_string('allparticipants'));

foreach ($roles as $role) {
	$rolestodisplay[$role->id] = $role->localname;
}

echo $OUTPUT->container_start('progressoverviewmenus');
echo get_string('role').'&nbsp;';
echo $OUTPUT->single_select($PAGE->url, 'role', $rolestodisplay, $roleselected);
echo $OUTPUT->container_end();

echo $OUTPUT->container_start('progressoverviewmenus');
$showexpiredusers = array(0 => get_string('yes'), 1 => get_string('no'), 2 => get_string('date'));
echo get_string('showinactiveusers', 'block_progress').'&nbsp;';
echo $OUTPUT->single_select($PAGE->url, 'active', $showexpiredusers, $showinactive);
$datewhere = '';

if($showinactive == 2)
{
	echo $OUTPUT->container_end();
	$mform_simple = new dateform(null, array('progressbarid' => $id, 
			'courseid'=>$courseid, 
			'page'=>$page, 
			'perpage'=>$perpage, 
			'active'=>$showinactive, 
			'group'=>$group));
	echo $mform_simple->display();
	echo $OUTPUT->container_start('progressoverviewmenus');

}

if($datesubmitted == 1)
{
	$data = $mform_simple->get_data();
	$timeactive = $data->activedate;
	
	$datewhere = 'AND ue.timestart <= \''. $timeactive.'\' AND (ue.timeend = 0 OR ue.timeend >= \''. $timeactive . '\')';
}


$params = array('contextid' => $context->id);

echo $OUTPUT->container_end();

// Apply group restrictions.
$params = array();
$groupjoin = '';
if ($group && $group != 0) {
    $groupjoin = 'JOIN {groups_members} g ON (g.groupid = :groupselected AND g.userid = u.id)';
    $params['groupselected'] = $group;
} else if ($groupuserid != 0 && !empty($groupids)) {
    $groupjoin = 'JOIN {groups_members} g ON (g.groupid IN ('.implode(',', $groupids).') AND g.userid = u.id)';
}

$userids = 0;
$userrecords = block_progress_get_user_records($rolewhere, $groupjoin, $datewhere, $showinactive);
block_progress_show_table($userrecords);

// Organise access to JS for progress bars.
$jsmodule = array('name' => 'block_progress', 'fullpath' => '/blocks/progress/module.js');
$arguments = array(array($progressblock->id), $userids);
$PAGE->requires->js_init_call('M.block_progress.setupScrolling', array(), false, $jsmodule);
$PAGE->requires->js_init_call('M.block_progress.init', $arguments, false, $jsmodule);

echo $OUTPUT->container_end();
echo $OUTPUT->footer();

function block_progress_get_user_records($rolewhere, $groupjoin, $datewhere, $showinactive)
{
	global $context, $course, $DB;
	$picturefields = user_picture::fields('u');
	
	$sql = "SELECT DISTINCT $picturefields, COALESCE(l.timeaccess, 0) AS lastonlinetime, ue.timestart, ue.timeend
	FROM {user} u
	JOIN {role_assignments} a ON (a.contextid = :contextid AND a.userid = u.id $rolewhere)
	JOIN {user_enrolments} ue ON ue.userid = u.id
	JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
	LEFT JOIN {user_lastaccess} l ON (l.courseid = e.courseid AND l.userid = u.id)
	WHERE u.deleted = 0 $datewhere
	";
	
	$params['contextid'] = $context->id;
	$params['courseid'] = $course->id;
	
	$userrecords = $DB->get_records_sql($sql, $params);
	if($showinactive == 1){
		extract_suspended_users($context, $userrecords);
	}
	return $userrecords;
}


function block_progress_show_table($userrecords)
{
	global $page, $CFG, $course, $events, $context, $modules, $progressconfig, $progressblock, $id, $OUTPUT, $PAGE, $userids, $perpage, $CFG;
	
	$userids = array_keys($userrecords);
	$users = array_values($userrecords);
	$numberofusers = count($users);
	$paged = $numberofusers > $perpage;
	if (!$paged) {
		$page = 0;
	}
	
	// Form for messaging selected participants.
	$formattributes = array('action' => $CFG->wwwroot.'/user/action_redir.php', 'method' => 'post', 'id' => 'participantsform');
	echo html_writer::start_tag('form', $formattributes);
	echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
	echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'returnto', 'value' => s($PAGE->url->out(false))));
	
	// Setup submissions table.
	$table = new flexible_table('mod-block-progress-overview');
	$table->pagesize($perpage, $numberofusers);
	$tablecolumns = array('fullname', 'lastonline', 'from','to', 'certificate', 'timespent', 'progressbar', 'progress', 'details');
	$table->define_columns($tablecolumns);
	$tableheaders = array(
			get_string('fullname'),
			get_string('lastonline', 'block_progress'),
			get_string('from'),
			get_string('to'),
			get_string('certificate', 'block_progress'),
			get_string('timespent', 'block_progress'),
			get_string('progressbar', 'block_progress'),
			get_string('progress', 'block_progress'),
			get_string('details', 'block_progress')
	
	);
	$table->define_headers($tableheaders);
	$table->sortable(true);
	
	$table->set_attribute('class', 'overviewTable');
	$table->column_style_all('padding', '5px');
	$table->column_style_all('text-align', 'left');
	$table->column_style_all('vertical-align', 'middle');
	$table->column_style('fullname', 'width', '10%');
	$table->column_style('lastonline', 'width', '10%');
	$table->column_style('progressbar', 'min-width', '200px');
	$table->column_style('progressbar', 'width', '*');
	$table->column_style('progressbar', 'padding', '0');
	$table->column_style('progress', 'text-align', 'center');
	$table->column_style('progress', 'width', '8%');
	$table->column_style('certificate', 'width', '10%');
	$table->column_style('timespent', 'width', '10%');
	$table->column_style('details', 'width', '5%');
	$table->column_style('to', 'width', '5%');
	$table->column_style('from', 'width', '5%');
	$table->no_sorting('picture');
	$table->no_sorting('progressbar');
	if ($paged) {
		$table->no_sorting('progress');
	}
	$table->define_baseurl($PAGE->url);
	$table->setup();
	
	// Sort the users (except by progress).
	global $sort;
	$sort = $table->get_sql_sort();
	$sortbyprogress = strncmp($sort, 'progress', 8) == 0;
	if (!$sort || ($paged && $sortbyprogress)) {
		$sort = 'firstname DESC';
	}
	if (!$sortbyprogress) {
		usort($users, 'block_progress_compare_rows');
	}
	
	// Get range of students for page.
	$startuser = $page * $perpage;
	$enduser = ($startuser + $perpage > $numberofusers) ? $numberofusers : ($startuser + $perpage);
	
	// Build table of progress bars as they are marked.
	$rows = array();
	for ($i = $startuser; $i < $enduser; $i++) {
		$namelink = html_writer::link($CFG->wwwroot.'/user/view.php?id='.$users[$i]->id.'&course='.$course->id, fullname($users[$i]));
		if (empty($users[$i]->lastonlinetime)) {
			$lastonline = get_string('never');
		} else {
			$lastonline = userdate($users[$i]->lastonlinetime, '%d-%m-%y %H:%m');
		}
		$userevents = block_progress_filter_visibility($events, $users[$i]->id, $context, $course);
		if (!empty($userevents)) {
			$attempts = block_progress_attempts($modules, $progressconfig, $userevents, $users[$i]->id, $course->id);
			$progressbar = block_progress_bar($modules, $progressconfig, $userevents, $users[$i]->id, $progressblock->id, $attempts,
					$course->id, true);
			$progressvalue = block_progress_percentage($userevents, $attempts, true);
			$progress = $progressvalue.'%';
		} else {
			$progressbar = get_string('no_visible_events_message', 'block_progress');
			$progressvalue = 0;
			$progress = '?';
		}
	
		//Get certificate code
		$certificate = getcertificatecode($course->id, $users[$i]->id);
	
		if($certificate->id > 0)
			$certificate_url = new moodle_url($certificate->filelink);
		else
			$certificate_url = "#";
	
		$certificatelink = html_writer::link($certificate_url, $certificate->id > 0 ? $certificate->code : get_string('nocertificate', 'block_progress'));

		$timespent = utils::format_timespend(block_progress_get_timespent($users[$i]->id, true, $course->id));
		
		$enrollmentfrom = ($users[$i]->timestart == 0) ? '-' : userdate($users[$i]->timestart, '%d-%m-%y');
		$enrollmentto = ($users[$i]->timeend == 0) ? '-' : userdate($users[$i]->timeend, '%d-%m-%y');
		
		$soobparameters = array('userid' => $users[$i]->id, 'courseid' => $course->id, 'id' => $id);
		$soobdetailslink = new moodle_url('/blocks/progress/overviewdetails.php', $soobparameters);
		$soobdetailsicon = $OUTPUT->pix_icon('calendar', 'details', 'block_progress', array('class' => 'nowicon'));
		$details = HTML_WRITER::link($soobdetailslink, $soobdetailsicon);
		
		$rows[] = array(
				'firstname' => $users[$i]->firstname,
				'lastname' => $users[$i]->lastname,
				'fullname' => $namelink,
				'lastonlinetime' => $users[$i]->lastonlinetime,
				'lastonline' => $lastonline,
				'from' => $enrollmentfrom,
				'to' => $enrollmentto,
				'certificate' => $certificatelink,
				'timespent' => $timespent,
				'progressbar' => $progressbar,
				'progressvalue' => $progressvalue,
				'progress' => $progress,
				'details' => $details
			);
	}
	
	// Build the table content and output.
	if ($sortbyprogress) {
		usort($rows, 'block_progress_compare_rows');
	}
	if ($numberofusers > 0) {
		foreach ($rows as $row) {
			$table->add_data(array(
					$row['fullname'], $row['lastonline'], $row['from'], $row['to'], $row['certificate'],$row['timespent'],
					$row['progressbar'], $row['progress'], $row['details']));
	
		}
	}
	$table->print_html();
	
	$perpageurl = clone($PAGE->url);
	if ($paged) {
		$perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
		echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $numberofusers)), array(), 'showall');
	} else if ($numberofusers > DEFAULT_PAGE_SIZE) {
		$perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
		echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');
	}
}


/**
 * Compares two table row elements for ordering.
 *
 * @param  mixed $a element containing name, online time and progress info
 * @param  mixed $b element containing name, online time and progress info
 * @return order of pair expressed as -1, 0, or 1
 */
function block_progress_compare_rows($a, $b) {
    global $sort;
	// Process each of the one or two orders.
    $orders = explode(',', $sort);
    
    foreach ($orders as $order) {

        // Extract the order information.
        $orderelements = explode(' ', trim($order));
        $aspect = $orderelements[0];
        $ascdesc = $orderelements[1];

        // Compensate for presented vs actual.
        switch ($aspect) {
            case 'name':
                $aspect = 'lastname';
                break;
            case 'lastonline':
                $aspect = 'lastonlinetime';
                break;
            case 'progress':
                $aspect = 'progressvalue';
                break;
        }

        // Check of order can be established.
        if (is_array($a)) {
            $first = $a[$aspect];
            $second = $b[$aspect];
        } else {
            $first = $a->$aspect;
            $second = $b->$aspect;
        }

        if (preg_match('/name/', $aspect)) {
            $first = strtolower($first);
            $second = strtolower($second);
        }

        if ($first < $second) {
            return $ascdesc == 'ASC' ? 1 : -1;
        }
        if ($first > $second) {
            return $ascdesc == 'ASC' ? -1 : 1;
        }
    }

    // If previous ordering fails, consider values equal.
    return 0;
}


