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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/ccv/lib.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/pdflib.php');

$courseid = required_param ( 'courseid', PARAM_INT );
$groupid = required_param ( 'groupid', PARAM_INT );
$fromdate = required_param ( 'fromdate', PARAM_INT );
$todate = required_param ( 'todate', PARAM_INT );

global $DB;

// Determine course and context.
$course = $DB->get_record ( 'course', array (
		'id' => $courseid
), '*', MUST_EXIST );
$context = block_ccv_get_course_context ( $courseid );

$sql2 = "SELECT groupname " .
		"FROM {block_addusers_groups} " .
		"WHERE id = ?";

$groupname = $DB->get_record_sql($sql2, array($groupid));

$sql2 = "SELECT id " .
		"FROM {groups} " .
		"WHERE name = ? and courseid = ?";

$groupid = $DB->get_record_sql($sql2, array($groupname->groupname, $courseid))->id;

$pdf = new TCPDF ( 'P', 'cm', 'A4', true, 'UTF-8', false );

// set document information
$pdf->SetCreator ( PDF_CREATOR );
$pdf->SetAuthor ( 'ik.plus' );
$pdf->SetTitle ( get_string ( 'report_a', 'block_ccv' ) );
$pdf->SetSubject ( get_string ( 'report_a', 'block_ccv'  ) );
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


$sql = "SELECT u.id, u.firstname, u.lastname, ue.timestart, ue.timeend
	FROM {user} u
	JOIN {groups_members} g ON (g.groupid = :groupid AND g.userid = u.id)
	JOIN {user_enrolments} ue ON ue.userid = u.id
	JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
	WHERE u.deleted = 0 AND ue.timestart >= :timestart AND ue.timestart <= :timeend 
	ORDER BY u.lastname";

$params['contextid'] = $context->id;
$params['courseid'] = $course->id;
$params['timestart'] = $fromdate;
$params['timeend'] = $todate;
$params['groupid'] = $groupid;

$userrecords = $DB->get_records_sql($sql, $params);

$output = '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;border-color:#ccc;border:none;margin:0px auto;}
.tg td{padding:20px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#fff;}
.tg th{padding:20px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#f0f0f0;}
.tg .tg-9hbo{font-weight:bold}
.tg .tg-b7b8{background-color:#F5F5F5}
.tg .red{background-color:#FF0000}	
.tg .green{background-color:#00ff11}
</style>
<p><h1>' . get_string ( 'report_a', 'block_ccv' ) . '</h1></p>

<table class="tg" style="undefined;table-layout: fixed; width: 522px">

  <tr>
    <td class="tg-yw4l">' . get_string ( 'report_name' , 'block_ccv' ).'<br></td>
    <td class="tg-yw4l">' . get_string ( 'report_a', 'block_ccv' ) .'</td>
  </tr>
  <tr>
    <td class="tg-b7b8">&nbsp;<br></td>
    <td class="tg-b7b8">' . get_string ( 'report_a_text', 'block_ccv' ) .'</td>
  </tr>
  <tr>
    <td class="tg-yw4l">' . get_string ( 'course' ) . '<br></td>
    <td class="tg-yw4l">'. $course->fullname . '</td>
  </tr>
  <tr>
    <td class="tg-b7b8">' . get_string ( 'group' , 'block_ccv' ) . '<br></td>
    <td class="tg-b7b8">'. $groupname->groupname . '</td>
  </tr>
  <tr>
    <td class="tg-yw4l">'.get_string ( 'report_period', 'block_ccv'  ).'<br></td>
    <td class="tg-yw4l">'.userdate($fromdate, '%d-%m-%y').' - '. userdate($todate, '%d-%m-%y') . '</td>
  </tr>

</table><br><br>
<table class="tg" style="undefined;table-layout: fixed">
  <tr>
    <th width="22px" class="tg-9hbo">#<br></th>
    <th width="100px" class="tg-9hbo">'.get_string ( 'firstname').'</th>
    <th width="100px" class="tg-9hbo">'.get_string ( 'lastname').'</th>
    <th width="100px" class="tg-9hbo">'.get_string ( 'days_in_course', 'block_ccv' ).'</th>
    <th width="100px" class="tg-9hbo">'.get_string ( 'startdate' ).'</th>
    <th width="100px" class="tg-9hbo">'.get_string ( 'enddate', 'block_ccv' ).'</th>
   </tr>';

	$count = 0;
	foreach ( $userrecords as $record ) {
		$count ++;
		if($count % 2 == 0)
			$class = 'tg-b7b8';
		else
			$class = 'tg-yw4l';
		
		if(floor(($record->timeend-1-$record->timestart)/86400) > 10)
			$color = 'red';
		else
			$color = 'green';
		
		$output .= '<tr>
		   	<td class="'.$class.'">'.$count.'<br></td>
		   	<td class="'.$class.'">'.$record->firstname.'<br></td>
		   	<td class="'.$class.'">'.$record->lastname.'<br></td>
		   	<td class="'.$color.'">'. floor(($record->timeend-1-$record->timestart)/86400).'<br></td>
		   	<td class="'.$class.'">'.userdate($record->timestart, '%d-%m-%y').'<br></td>
		   	<td class="'.$class.'">'.userdate($record->timeend-1, '%d-%m-%y').'<br></td>
			</tr>';
	}
	$output .= '</table>';

	// Print text using writeHTMLCell()
	$pdf->writeHTMLCell ( 0, 0, '', '', $output, 0, 1, 0, true, '', true );

	// ---------------------------------------------------------

	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	$pdf->Output ( $course->fullname . ' - ' . get_string ( 'report_a', 'block_ccv' ) . '.pdf', 'D' );