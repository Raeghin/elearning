<?php
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/pdflib.php');

function block_ccv_get_groups()
{
	global $DB;
	
	$sql = "SELECT DISTINCT g.id AS groupid, uid.data AS groupname
	FROM {block_addusers_groups} g
	RIGHT JOIN {user_info_data} uid ON uid.data = g.groupname
	RIGHT JOIN {user_info_field} uif ON uif.id = uid.fieldid
	WHERE uif.shortname = 'Opleidernaam' AND uid.data != ''";
	
	return ($DB->get_records_sql($sql));
}

function block_ccv_get_courses()
{
	global $DB;
	$sql = "SELECT c.id, c.fullname, c.shortname " .
			"FROM {course} c";

	return $DB->get_records_sql($sql);
}

function block_ccv_generate_report($groupid, $courseid, $report, $fromdate, $todate)
{
	switch ($report) {
		case 'a':
			$parameters = array('courseid' => $courseid, 'groupid' => $groupid, 'fromdate'=> $fromdate, 'todate'=>$todate);
			$url = new moodle_url('/blocks/ccv/generate_report_a.php', $parameters);
			$label = get_string('download');
			$options = array('class' => 'overviewButton');
			
			return single_button($url, $label, 'post', $options);
			
			break;
		case 'b':
			return 'b';
			break;
		case 'c':
			return 'c';
			break;
		case 'd':
			return 'd';
			break;
	}
}

/**
 * Gets the course context, allowing for old and new Moodle instances.
 *
 * @param int $courseid The course ID
 * @return stdClass The context object
 */
function block_ccv_get_course_context($courseid) {
	if (class_exists('context_course')) {
		return context_course::instance($courseid);
	} else {
		return get_context_instance(CONTEXT_COURSE, $courseid);
	}
}

/**
 * Get Name of the group (global) by id
 * @param $groupid
 * @return mixed|boolean
 */
function block_ccv_get_group_name_by_group_id($groupid)
{
	global $DB;
	$sql = "SELECT groupname " .
			"FROM {block_addusers_groups} " .
			"WHERE id = ?";
	
	return $DB->get_record_sql($sql, array($groupid));
}

/**
 * Get course group id by global group name. Returns -1 if global group not present in course
 * @param $groupname
 * @param $courseid
 */
function block_ccv_get_course_group_id($groupname, $courseid)
{
	global $DB;
	$sql = "SELECT id " .
			"FROM {groups} " .
			"WHERE name = ? and courseid = ?";
	
	$result = $DB->get_record_sql($sql, array($groupname->groupname, $courseid));
	if($result == null)
		return -1;
	else 
		return $result->id;
}

function block_ccv_generate_pdf($title)
{
	$pdf = new TCPDF ( 'P', 'cm', 'A4', true, 'UTF-8', false );
	
	// set document information
	$pdf->SetCreator ( PDF_CREATOR );
	$pdf->SetAuthor ( 'ik.plus' );
	$pdf->SetTitle ( $title );
	$pdf->SetSubject ( $title );
	$pdf->SetKeywords ( 'ik.plus' );
	
	$pdf->setPrintHeader ( false );
	$pdf->setPrintFooter ( false );
	
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );
	
	// set margins
	$pdf->SetMargins ( 1, 1, 1 );
	$pdf->SetHeaderMargin ( 2 );
	$pdf->SetFooterMargin ( 2 );
	
	// set auto page breaks
	$pdf->SetAutoPageBreak ( TRUE, 2 );
	
	// set default font subsetting mode
	$pdf->setFontSubsetting ( true );
	
	// Set font
	// dejavusans is a UTF-8 Unicode font, if you only need to
	// print standard ASCII chars, you can use core fonts like
	// helvetica or times to reduce file size.
	$pdf->SetFont ( 'helvetica', '', 8, '', true );
	
	// Add a page
	// This method has several options, check the source code documentation for more information.
	$pdf->AddPage ();
	
	return $pdf;
}

function block_ccv_get_page_header($title, $data)
{
	$output = '<style type="text/css">
				.tg  {border-collapse:collapse;border-spacing:0;border-color:#ccc;border:none;margin:0px auto;}
				.tg td{padding:40px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#fff;}
				.tg th{padding:40px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#f0f0f0;}
				.tg .tg-9hbo{font-weight:bold}
				.tg .tg-b7b8{background-color:#F5F5F5}
				.tg .red{background-color:#FF0000}
				.tg .green{background-color:#00ff11}
				</style><p><h1>' . $title . '</h1></p>
				<table class="tg" style="undefined;table-layout: fixed; width: 522px">';
	
	$count = 0;
	foreach($data as $input)
	{
		$count++;
		if($count % 2 == 0)
			$style = 'tg-yw41';
		else
			$style = 'tg-b7b8';
		
		$output .= '<tr><td class="' . $style . '">' . $input->title . '<br></td>';
		$output .= '<td class="' . $style . '">' . $input->value .'</td></tr>';
	}
	
	$output .= '</table><br/><br/>';
	
	return $output;
}
	
function block_ccv_get_data_by_course_group($groupid, $fromdate, $todate, $courseid)
{
	global $DB;
	$sql = "SELECT u.id, u.firstname, u.lastname, ue.timestart, ue.timeend, g.name, g.description
			FROM {user} u
			JOIN {groups_members} gm ON (gm.groupid = :groupid AND gm.userid = u.id)
			JOIN {groups} g ON (g.id = gm.groupid AND g.courseid = :courseid)
			JOIN {user_enrolments} ue ON ue.userid = u.id
			JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid2)
			WHERE u.deleted = 0 AND ue.timeend >= :timestart AND ue.timeend <= :timeend
			ORDER BY u.lastname";
	
	$params['courseid'] = $courseid;
	$params['courseid2'] = $courseid;
	$params['timestart'] = $fromdate;
	$params['timeend'] = $todate;
	$params['groupid'] = $groupid;
	
	return $DB->get_records_sql($sql, $params);
}

function block_ccv_get_data_by_course($fromdate, $todate, $courseid)
{
	global $DB;
	$sql = "SELECT u.id, u.firstname, u.lastname, ue.timestart, ue.timeend, g.name, g.description
		FROM {user} u
		JOIN {groups_members} gm ON (gm.userid = u.id)
		JOIN {groups} g ON (g.id = gm.groupid AND g.courseid = :courseid)
		JOIN {user_enrolments} ue ON ue.userid = u.id
		JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid2)
		WHERE u.deleted = 0 AND ue.timeend >= :timestart AND ue.timeend <= :timeend
		ORDER BY u.lastname";
	
	$params['courseid'] = $courseid;
	$params['courseid2'] = $courseid;
	$params['timestart'] = $fromdate;
	$params['timeend'] = $todate;
	
	return $DB->get_records_sql($sql, $params);
}