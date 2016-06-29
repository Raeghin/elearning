<?php
require_once (dirname ( __FILE__ ) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/addusers/lib.php');
class block_addusers extends block_base {
	
	/**
	 * Sets the block title
	 *
	 * @return void
	 */
	public function init() {	
		$this->title = get_string ('addusers', 'block_addusers' );
	}
	
	/**
	 * Creates the blocks main content
	 *
	 * @return string
	 */
	public function get_content() {
		global $USER, $COURSE, $CFG, $OUTPUT, $DB;
		
		// Guests do not have any progress. Don't show them the block.
		if (!isloggedin() or isguestuser()) {
			return $this->content;
		}
		
		if ($this->content !== null) {
			return $this->content;
		}
		
		if($USER->profile['Opleider'] == 0)
		{
			return $this->content;
		}
		setlocale(LC_MONETARY, 'nl_NL');
		$this->content = new stdClass ();
		$this->content->text = "<b>" . get_string('institution' , 'block_addusers') . ':</b> ' . $USER->profile['Opleidernaam'] . "<br/>";
		$this->content->text .= "<b>" .  get_string('credits' , 'block_addusers') . ':</b> ' . money_format('%i', (block_addusers_get_credits($USER->profile['Opleidernaam']) / 100));
		
		$this->content->text .= "<p>";
		$menulist = array();
		//Credit History Overview
		$menulist[] = html_writer::link(new moodle_url('/blocks/addusers/credithistoryoverview.php', array()), get_string('credit_history', 'block_addusers'));
		$menulist[] = html_writer::link(new moodle_url('/blocks/addusers/adduser.php', array()), get_string('add_user', 'block_addusers'));
		$menulist[] = html_writer::link(new moodle_url('/blocks/addusers/listusers.php', array()), get_string('list_users', 'block_addusers'));
		
		if(is_siteadmin($USER->id))
		{
			$menulist[] = html_writer::link(new moodle_url('/blocks/addusers/coursecosts.php', array()), get_string('course_costs', 'block_addusers'));
			$menulist[] = html_writer::link(new moodle_url('/blocks/addusers/usercredits.php', array()), get_string('user_credits', 'block_addusers'));
		}
		
		$this->content->text .= html_writer::alist($menulist, array("style"=>"list-style-type: square;"));
		
		$this->content->text .= "</p>";
		return $this->content;
	}
	
	public function instance_allow_multiple() {
		return true;
	}
}
