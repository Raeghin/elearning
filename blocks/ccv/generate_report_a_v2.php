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

if($groupid > 0)
{
	$groupname = block_ccv_get_group_name_by_group_id($groupid);
} else {
	$groupname = new stdClass();
	$groupname->groupname = get_string('all_groups', 'block_ccv');
}

$pdf = block_ccv_generate_pdf(get_string('report_a', 'block_ccv'));
$output = '';


if($courseid > 0)
{
	$course = get_course($courseid);
	
	if($groupid > 0)
	{
		$coursegroupid = block_ccv_get_course_group_id($groupname, $courseid);
		$userrecords = block_ccv_get_data_by_course_group($groupid, $fromdate, $todate, $courseid);
	} else if ($groupid == 0){
		$userrecords = block_ccv_get_data_by_course($fromdate, $todate, $courseid);
	} else {
		$userrecords = null;
	}
	$output .= generate_header($course->fullname, $groupname->groupname, $fromdate, $todate);
	$output .= generate_body($userrecords);
} else {
	$courses = get_courses();
	$output .= generate_header(get_string('all_courses', 'block_ccv'), get_string('all_groups', 'block_ccv'), $fromdate, $todate);
	foreach($courses as $course)
	{
		if($groupid > 0)
		{
			$coursegroupid = block_ccv_get_course_group_id($groupname, $course->id);
			$userrecords = block_ccv_get_data_by_course_group($groupid, $fromdate, $todate, $course->id);
		} else if ($groupid == 0){
			$userrecords = block_ccv_get_data_by_course($fromdate, $todate, $course->id);
		}
		
		$output .= generate_body($userrecords, $course->fullname);
	}
}

function generate_header($coursename, $groupname, $fromdate, $todate)
{
	$tabledata = array();
	$tabledata[] = (object) ['title' => get_string ( 'report_name' , 'block_ccv' ), 'value' => get_string ( 'report_a', 'block_ccv' )];
	$tabledata[] = (object) ['title' => '&nbsp;', 'value' => get_string ( 'report_a_text', 'block_ccv' )];
	$tabledata[] = (object) ['title' => get_string ( 'course' ), 'value' => $coursename];
	$tabledata[] = (object) ['title' => get_string ( 'group' , 'block_ccv' ), 'value' => $groupname];
	$tabledata[] = (object) ['title' => get_string ( 'report_period', 'block_ccv'  ), 'value' => userdate($fromdate, '%d-%m-%y').' - '. userdate($todate, '%d-%m-%y')];
	
	return block_ccv_get_page_header(get_string ( 'report_a', 'block_ccv' ), $tabledata);
}

function generate_body($userrecords, $coursename = null, $groupname = null)
{
	if($userrecords == null)
		return '';
	
	$output = '';
	
	if($coursename )
	{
		$output .= '<h2>' . $coursename . '</h2><br/>';
	}
		
	$output .= '<table class="tg">
	  <tr>
	    <th width="22px" class="tg-9hbo">#<br></th>
	    <th width="90px" class="tg-9hbo">'.get_string ( 'firstname').'</th>
	    <th width="90px" class="tg-9hbo">'.get_string ( 'lastname').'</th>
	    <th width="90px" class="tg-9hbo">'.get_string ( 'days_in_course', 'block_ccv' ).'</th>
	    <th width="90px" class="tg-9hbo">'.get_string ( 'startdate' ).'</th>
	    <th width="90px" class="tg-9hbo">'.get_string ( 'enddate', 'block_ccv' ).'</th>';
	if($groupname)
		$output .= '<th width="90px" class="tg-9hbo">'.get_string ( 'group' , 'block_ccv').'</th>';
	 $output .= '</tr>';

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
		   	<td class="'.$class.'">'.$count.'</td>
		   	<td class="'.$class.'">'.$record->firstname.'</td>
		   	<td class="'.$class.'">'.$record->lastname.'</td>
		   	<td class="'.$color.'">'. floor(($record->timeend-1-$record->timestart)/86400).'</td>
		   	<td class="'.$class.'">'.userdate($record->timestart, '%d-%m-%y').'</td>
		   	<td class="'.$class.'">'.userdate($record->timeend-1, '%d-%m-%y').'</td>';
		if($coursename)
		{
			$group = $record->description != '' ? $record->description : $record->name;
			$output .= '<td class="'.$class.'">' . $group . '</td>';
		} else if($groupname)
			$output .= '<td class="'.$class.'">'.$groupname.'</td>';
		
		$output .=	'</tr>';
	}
	$output .= '</table><br/><br/>';
	return $output;
}

$pdf->writeHTMLCell ( 0, 0, '', '', $output, 0, 1, 0, true, '', true );
$pdf->Output ( $course->fullname . ' - ' . get_string ( 'report_a', 'block_ccv' ) . '.pdf', 'D' );