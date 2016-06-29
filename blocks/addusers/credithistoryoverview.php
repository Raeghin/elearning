<?php
// Include required files.
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once ($CFG->dirroot . '/blocks/addusers/lib.php');
require_once ($CFG->libdir . '/tablelib.php');

define ( 'DEFAULT_PAGE_SIZE', 20 );
define ( 'SHOW_ALL_PAGE_SIZE', 5000 );


// Gather form data.
$userid = $USER->id;
$groupid = block_addusers_get_groupid($USER->profile['Opleidernaam']);
$page = optional_param ( 'page', 0, PARAM_INT ); // Which page to show.
$perpage = optional_param ( 'perpage', DEFAULT_PAGE_SIZE, PARAM_INT ); // How many per page.

$PAGE->set_url ( '/blocks/addusers/credithistoryoverview.php', array (
		'page' => $page,
		'perpage' => $perpage 
) );
$PAGE->set_context ( context_system::instance () );
$title = get_string ( 'credit_history', 'block_addusers' );
$PAGE->set_title ( $title );
$PAGE->set_heading ( $title );
$PAGE->navbar->add ( $title );
$PAGE->set_pagelayout ( 'report' );
$sort = '';

// Start page output.
echo $OUTPUT->header ();
echo $OUTPUT->heading ( $title, 2 );
echo $OUTPUT->container_start ( 'block_history' );

$history = block_addusers_get_credit_history ( $groupid );

block_addusers_show_table ( $history );

echo $OUTPUT->container_end ();
echo $OUTPUT->footer();

function block_addusers_show_table($history) {
	global $PAGE, $page, $CFG, $perpage, $OUTPUT;
	$numberofentries = count ( $history );
	$paged = $numberofentries > $perpage;
	
	if (! $paged) {
		$page = 0;
	}
	
	// Setup table.
	$table = new flexible_table ( 'block-buyusers-history-overview' );
	$table->pagesize ( $perpage, $numberofentries );
	$tablecolumns = array (
			'date',
			'amount',
			'coursename',
			'user',
			'comment'
	);
	
	$table->define_columns ( $tablecolumns );
	$tableheaders = array (
			get_string ( 'date' ),
			get_string ( 'mutation', 'block_addusers' ),
			get_string ( 'course' ),
			get_string ( 'user' ),
			get_string ( 'comment', 'block_addusers')
	);
	$table->define_headers ( $tableheaders );
	$table->sortable ( true );
	
	$table->set_attribute ( 'class', 'overviewTable' );
	$table->column_style_all ( 'padding', '5px' );
	$table->column_style_all ( 'text-align', 'left' );
	$table->column_style_all ( 'vertical-align', 'middle' );
	$table->column_style ( 'date', 'width', '20%' );
	$table->column_style ( 'amount', 'width', '10%' );
	$table->column_style ( 'coursename', 'width', '30%' );
	$table->column_style ( 'user', 'width', '20%' );
	$table->column_style ( 'comment', 'width', '20%' );
	
	$table->define_baseurl($PAGE->url);
	$table->setup();
	
	// Get range of rows for page.
	$start = $page * $perpage;
	$end = ($start + $perpage > $numberofentries) ? $numberofentries : ($start + $perpage);
	
	$rows = array_values($history);
	$tablerows = array();
	for ($i = $start; $i < $end; $i++) {
		setlocale(LC_MONETARY, 'nl_NL');
		$tablerows[] = array(
			'date' => userdate($rows[$i]->dateofpurchase, '%d-%m-%y %H:%m'),
			'amount' => money_format('%i', ($rows[$i]->amount / 100)),
			'coursename' => $rows[$i]->coursename,
			'user' => $rows[$i]->firstname . ' ' . $rows[$i]->lastname,
			'comment' => $rows[$i]->comment);
			
	}
	
	if ($numberofentries > 0) {
		foreach ($tablerows as $row) {
			$table->add_data(array(
				$row['date'], $row['amount'], $row['coursename'], $row['user'], $row['comment']));
		}
	}
	
	$table->print_html ();
	
	$perpageurl = clone($PAGE->url);
	
	if ($paged) {
		$perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
		echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $numberofentries)), array(), 'showall');
	} else if ($numberofentries > DEFAULT_PAGE_SIZE) {
		$perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
		echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');
	}
}