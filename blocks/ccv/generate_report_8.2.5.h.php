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
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/pdflib.php');
require_once ($CFG->dirroot . '/blocks/ccv/lib.php');
require_once ($CFG->dirroot . '/blocks/addusers/lib.php');
require_once ($CFG->dirroot . '/blocks/progress/lib.php');

$courseid = required_param ( 'courseid', PARAM_INT );
$groupid = required_param ( 'groupid', PARAM_INT );
$fromdate = required_param ( 'fromdate', PARAM_INT );
$todate = required_param ( 'todate', PARAM_INT );
$addtime = required_param ( 'addtime', PARAM_INT );
$sort = required_param ( 'sort', PARAM_TEXT );

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
$pdf->SetTitle ( get_string ( 'report_h', 'block_ccv' ) );
$pdf->SetSubject ( get_string ( 'report_h', 'block_ccv'  ) );
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
	WHERE u.deleted = 0 AND ue.timeend >= :timestart AND ue.timeend <= :timeend 
	ORDER BY u.lastname";

$params['contextid'] = $context->id;
$params['courseid'] = $course->id;
$params['timestart'] = $fromdate;
$params['timeend'] = $todate;
$params['groupid'] = $groupid;

$userrecords = $DB->get_records_sql($sql, $params);
$timereq = block_addusers_get_course_details($course->id);

$output = '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;border-color:#ccc;border:none;margin:0px auto;}
.tg td{padding:20px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#fff;}
.tg th{padding:20px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#f0f0f0;}
.tg .tg-9hbo{font-weight:bold}
.tg .tg-b7b8{background-color:#F5F5F5}
.tg .red{background-color:#FF0000}	
.tg .green{background-color:#00ff11}
</style>
<p><h1>' . get_string ( 'report_h', 'block_ccv' ) . '</h1></p>

<table class="tg" style="undefined;table-layout: fixed; width: 522px">

  <tr>
    <td class="tg-yw4l">' . get_string ( 'report_name' , 'block_ccv' ).'<br></td>
    <td class="tg-yw4l">' . get_string ( 'report_h', 'block_ccv' ) .'</td>
  </tr>
  <tr>
    <td class="tg-b7b8">&nbsp;<br></td>
    <td class="tg-b7b8">' . get_string ( 'report_h_text', 'block_ccv' ) .'</td>
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
  <tr>
    <td class="tg-b7b8">' . get_string ( 'timereq' , 'block_ccv' ) . '<br></td>
    <td class="tg-b7b8">'. $timereq->hours_required . '</td>
  </tr>

</table><br><br>
<table class="tg" style="undefined;table-layout: fixed">
  <tr>
    <th width="22px" class="tg-9hbo">#<br></th>
    <th width="80px" class="tg-9hbo">'.get_string ( 'firstname').'</th>
    <th width="80px" class="tg-9hbo">'.get_string ( 'lastname').'</th>
    <th width="80px" class="tg-9hbo">'.get_string ( 'startdate' ).'</th>
    <th width="80px" class="tg-9hbo">'.get_string ( 'enddate', 'block_ccv' ).'</th>
    <th width="100px" class="tg-9hbo">'.get_string ( 'time_in_course', 'block_ccv' ).'</th>
    <th width="100px" class="tg-9hbo">'.get_string ( 'finished', 'block_ccv' ).'</th>
   </tr>';

	$count = 0;
	
	if($sort == 'test')
	{
		$totaltime = 0;
		$count = 0;
		foreach ( $userrecords as $record ) {
			$time = block_progress_get_timespent($record->id, true, $course->id);
			
			
			$certificate = getcertificatecode($course->id, $record->id);
			echo $certificate->id;
			if($certificate->id <> 0)
			{
				$count = $count + 1;
				$totaltime = $totaltime + $time;
			}
		}
		
		echo 'Totaal aantal geslaagde gebruikers: ' . $count;
		echo ' Totale tijd ' . utils::format_timespend($totaltime);
		echo ' Gemiddelde tijd ' . utils::format_timespend($totaltime / $count);
	}
	
	if($sort == 'time')
	{
		foreach ( $userrecords as $record ) {
			$time = block_progress_get_timespent($record->id, true, $course->id);
			$record->time = $time;
		}
		
		usort($userrecords, function($a, $b)
		{
			return $a->time - $b->time;
		});
	}
	
	
	foreach ( $userrecords as $record ) {
		$count ++;
		if($count % 2 == 0)
			$class = 'tg-b7b8';
		else
			$class = 'tg-yw4l';
		
		$certificate = getcertificatecode($course->id, $record->id);
			
		if($certificate->id == 0)
		{
			$color = 'red';
			$text = get_string('not_finished', 'block_ccv');
		}
		else
		{
			$color = 'green';
			$text = get_string('finished', 'block_ccv');
		}	
		
		$time = block_progress_get_timespent($record->id, true, $course->id);
	
		if($addtime)
		{
			while(0 < $time && $time < ($timereq->hours_required * 3600))
			{
				$time = $time + 3600;
			}
		}
		
		$timespent = utils::format_timespend($time);
		
		if($time < ($timereq->hours_required * 3600) || $timespent == get_string('none'))
			$tcolor = 'red';
		else
			$tcolor = 'green';
			
		$output .= '<tr>
		   	<td class="'.$class.'">'.$count.'<br></td>
		   	<td class="'.$class.'">'.$record->firstname.'<br></td>
		   	<td class="'.$class.'">'.$record->lastname.'<br></td>
		   	<td class="'.$class.'">'.userdate($record->timestart, '%d-%m-%y').'<br></td>
		   	<td class="'.$class.'">'.userdate($record->timeend-1, '%d-%m-%y').'<br></td>
		   	<td class="'.$tcolor.'">'.$timespent.'<br></td>
		   	<td class="'.$color.'">'.$text.'<br></td>		
			</tr>';
	}
	$output .= '</table>';

	// Print text using writeHTMLCell()
	$pdf->writeHTMLCell ( 0, 0, '', '', $output, 0, 1, 0, true, '', true );

	// ---------------------------------------------------------

	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	$pdf->Output ( $course->fullname . ' - ' . get_string ( 'report_h', 'block_ccv' ) . '.pdf', 'D' );