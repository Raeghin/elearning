<?php

require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/ccv/lib.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once ($CFG->libdir . '/pdflib.php');
require_once ($CFG->dirroot . '/blocks/addusers/lib.php');

require_once ($CFG->dirroot . '/blocks/progress/lib.php');

$courseid = required_param ( 'courseid', PARAM_INT );
$groupid = required_param ( 'groupid', PARAM_INT );
$fromdate = required_param ( 'fromdate', PARAM_INT );
$todate = required_param ( 'todate', PARAM_INT );
$addtime = required_param ( 'addtime', PARAM_INT );


if($groupid > 0)
	$groupname = block_ccv_get_group_name_by_group_id($groupid);
else {
	$groupname = new stdClass();
	$groupname->groupname = get_string('all_groups', 'block_ccv');
}

$pdf = block_ccv_generate_pdf(get_string('report_b', 'block_ccv'));
$output = '';

if($courseid > 0)
{
	$course = get_course($courseid);
	
	if($groupid > 0)
	{
		$coursegroupid = block_ccv_get_course_group_id($groupname, $courseid);
		$userrecords = block_ccv_get_data_by_course_group($coursegroupid, $fromdate, $todate, $courseid);
	} else if ($groupid == 0)
		$userrecords = block_ccv_get_data_by_course($fromdate, $todate, $courseid);
	else
		$userrecords = null;
	
	$output .= generate_header($course->fullname, $groupname->groupname, $fromdate, $todate);
	$timereq = block_addusers_get_course_details($course->id);
	if($groupid > 0)
		$output .= generate_body($userrecords, $course->fullname, true, $timereq->hours_required, $course->id, $addtime);
	else if ($groupid == 0)
		$output .= generate_body($userrecords, $course->fullname, false, $timereq->hours_required, $course->id, $addtime);
	
} else {
	$courses = get_courses();
	
	
	if($groupid > 0)
	{
		$output .= generate_header(get_string('all_courses', 'block_ccv'), $groupname->groupname, $fromdate, $todate);
	} else if ($groupid == 0){
		$output .= generate_header(get_string('all_courses', 'block_ccv'), get_string('all_groups', 'block_ccv'), $fromdate, $todate);
	}
	
	foreach($courses as $course)
	{
		if($course-> id == 9)
			continue;
		
		$timereq = block_addusers_get_course_details($course->id);
		if($groupid > 0)
		{
			$coursegroupid = block_ccv_get_course_group_id($groupname, $course->id);
			$userrecords = block_ccv_get_data_by_course_group($coursegroupid, $fromdate, $todate, $course->id);
			$output .= generate_body($userrecords, $course->fullname, false, $timereq->hours_required, $course->id, $addtime);
		} else if ($groupid == 0){
			$userrecords = block_ccv_get_data_by_course($fromdate, $todate, $course->id);
			$output .= generate_body($userrecords, $course->fullname, true, $timereq->hours_required, $course->id, $addtime);
		}
	}
}

function generate_header($coursename, $groupname, $fromdate, $todate)
{
	
	$tabledata = array();
	$tabledata[] = (object) ['title' => get_string ( 'report_name' , 'block_ccv' ), 'value' => get_string ( 'report_b', 'block_ccv' )];
	$tabledata[] = (object) ['title' => '&nbsp;', 'value' => get_string ( 'report_b_text', 'block_ccv' )];
	$tabledata[] = (object) ['title' => get_string ( 'course' ), 'value' => $coursename];
	$tabledata[] = (object) ['title' => get_string ( 'group' , 'block_ccv' ), 'value' => $groupname];
	$tabledata[] = (object) ['title' => get_string ( 'report_period', 'block_ccv'  ), 'value' => userdate($fromdate, '%d-%m-%y').' - '. userdate($todate, '%d-%m-%y')];
	
	
	return block_ccv_get_page_header(get_string ( 'report_b', 'block_ccv' ), $tabledata);
}

function generate_body($userrecords, $coursename = null, $groupname, $timereq, $courseid, $addtime)
{
	if($userrecords == null)
		return '';
	
	$output = '';
	
	if($coursename )
	{
		$output .= '<h2>' . $coursename . ' ' . '</h2><br/><p>' . get_string('timereq', 'block_ccv') . ':' . $timereq . ' ' . get_string('hours') . '</h2><br/>';
	}
		
	$output .= '<table class="tg">
	  			<tr>
    				<th width="22px" class="tg-9hbo">#<br></th>
   					<th width="100px" class="tg-9hbo">'.get_string ( 'firstname').'</th>
    				<th width="100px" class="tg-9hbo">'.get_string ( 'lastname').'</th>
    				<th width="100px" class="tg-9hbo">'.get_string ( 'time_in_course', 'block_ccv' ).'</th>';
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
		
		$time = block_progress_get_timespent($record->id, true, $courseid);
		$timespent = utils::format_timespend($time);
			
		$certificate = getcertificatecode($courseid, $record->id);
			
		if($addtime && $certificate->id > 0)
		{
			
			while(($time/3600) < $timereq)
			{
				$time = $time + 3600;
			}
			$timespent = utils::format_timespend($time);
		}
		
		if($timespent < $timereq || $timespent == get_string('none'))
			$color = 'red';
		else
			$color = 'green';
		
		$output .= '<tr>
		   	<td class="'.$class.'">'.$count.'<br></td>
		   	<td class="'.$class.'">'.$record->firstname.'<br></td>
		   	<td class="'.$class.'">'.$record->lastname.'<br></td>
		   	<td class="'.$color.'">'. $timespent.'<br></td>';
		if($groupname)
		{
			$group = $record->description != '' ? $record->description : $record->name;
			$output .= '<td class="'.$class.'">'. $group.'<br></td>';
		}
		
		$output .= '</tr>';
	}
	$output .= '</table><br/>';
	return $output;
}

$pdf->writeHTMLCell ( 0, 0, '', '', $output, 0, 1, 0, true, '', true );
$pdf->Output ( $course->fullname . ' - ' . get_string ( 'report_b', 'block_ccv' ) . '.pdf', 'D' );