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
require_once ($CFG->dirroot . '/blocks/progress/lib.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/pdflib.php');

$courseid = required_param ( 'courseid', PARAM_INT );
$userid = required_param ( 'userid', PARAM_INT );


// Determine course and context.
$course = $DB->get_record ( 'course', array (
		'id' => $courseid
), '*', MUST_EXIST );
$context = block_progress_get_course_context ( $courseid );

$user = $DB->get_record ( 'user', array (
		'id' => $userid
), '*', MUST_EXIST );

$pdf = new TCPDF ( 'P', 'cm', 'A4', true, 'UTF-8', false );

// set document information
$pdf->SetCreator ( PDF_CREATOR );
$pdf->SetAuthor ( 'ik.plus' );
$pdf->SetTitle ( get_string ( 'student_details', 'block_progress', $user->firstname . ' ' . $user->lastname ) );
$pdf->SetSubject ( get_string ( 'student_details', 'block_progress', $user->firstname . ' ' . $user->lastname ) );
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

// Get Last Online Time
$sql = "SELECT COALESCE(l.timeaccess, 0) AS lastonlinetime
FROM {user} u
JOIN {role_assignments} a ON (a.contextid = :contextid AND a.userid = u.id)
LEFT JOIN {user_lastaccess} l ON (l.courseid = :courseid AND l.userid = u.id)
		WHERE u.id = :userid";
$params ['contextid'] = $context->id;
$params ['courseid'] = $course->id;
$params ['userid'] = $user->id;
$lastonlinetime = $DB->get_record_sql ( $sql, $params )->lastonlinetime;

if (empty ( $lastonlinetime )) {
	$lastonline = get_string ( 'never' );
} else {
	$lastonline = userdate ( $lastonlinetime );
}
$timespent = utils::format_timespend ( block_progress_get_timespent ( $user->id, true, $course->id ) );

$timetable = block_progress_get_timespent ( $user->id, false, $course->id );


$output = '<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;border-color:#ccc;border:none;margin:0px auto;}
.tg td{padding:20px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#fff;}
.tg th{padding:20px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#f0f0f0;}
.tg .tg-9hbo{font-weight:bold}
.tg .tg-b7b8{background-color:#F5F5F5}
</style>
<p><h1>' . get_string ( 'student_details', 'block_progress', $user->firstname . ' ' . $user->lastname ) . '</h1></p>		
		
<table class="tg" style="undefined;table-layout: fixed; width: 400px">
  <tr>
    <th class="tg-9hbo" colspan="2">'.get_string ( 'details', 'block_progress' ).'<br></th>
  </tr>
  <tr>
    <td class="tg-yw4l">'.get_string ( 'fullname' ).'<br></td>
    <td class="tg-yw4l">'.$user->firstname . ' ' . $user->lastname .'</td>
  </tr>
  <tr>
    <td class="tg-b7b8">'.get_string ( 'course' ).'<br></td>
    <td class="tg-b7b8">'.$course->fullname.'</td>
  </tr>
  <tr>
    <td class="tg-yw4l">' . get_string ( 'lastonline', 'block_progress' ) . '<br></td>
    <td class="tg-yw4l">'.$lastonline.'</td>
  </tr>
  <tr>
    <td class="tg-b7b8">'.get_string ( 'timespent', 'block_progress' ).'<br></td>
    <td class="tg-b7b8">'.$timespent.'</td>
  </tr>
</table><br><br>
<table class="tg" style="undefined;table-layout: fixed; width: 400px">
  <tr>
    <th width="22px" class="tg-9hbo">#<br></th>
    <th width="126px" class="tg-9hbo">'.get_string ( 'start', 'block_progress' ).'</th>
    <th width="126px" class="tg-9hbo">'.get_string ( 'end', 'block_progress' ).'</th>
    <th width="126px" class="tg-9hbo">'.get_string ( 'time', 'block_progress' ).'</th>    		
  </tr>';

$count = 0;
foreach ( $timetable as $time ) {
	$count ++;
	if($count % 2 == 0)
		$class = 'tg-b7b8';
	else
		$class = 'tg-yw4l';
	$output .= '<tr>
    <td class="'.$class.'">'.$count.'<br></td>
    <td class="'.$class.'">'.userdate ( $time->start_date ) .'</td>
    <td class="'.$class.'">'.userdate ( $time->start_date + $time->timespendtime ).'<br></td>
    <td class="'.$class.'">'.utils::format_timespend ( $time->timespendtime ).'</td>
  </tr>';
}	
$output .= '</table>';

// Print text using writeHTMLCell()
$pdf->writeHTMLCell ( 0, 0, '', '', $output, 0, 1, 0, true, '', true );

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output ( $course->fullname . ' - ' . $user->firstname . ' ' . $user->lastname . '.pdf', 'D' );
