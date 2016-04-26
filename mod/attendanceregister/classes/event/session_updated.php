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
 * The mod_attendanceregister event.
 *
 * @package    mod_attendanceregister
 * @copyright  2016 hsien
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace mod_attendanceregister\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The mod_attendanceregister event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - PUT INFO HERE
 * }
 *
 * @since     Moodle 2.7
 * @copyright 2016 hsien
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class session_updated extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'u'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'attendanceregister_session';
    }
 
    public static function get_name() {
		return get_string('eventsessionupdate', 'mod_attendanceregister');
    }
 
    public function get_description() {
        return "user id:{$this->userid} {$this->other['action']} attendanceregister session (course id:{$this->objectid},module id:{$this->contextinstanceid})";
    }
 
    public function get_url() {
        return new \moodle_url('/mod/attendanceregister/view.php', array('id' => $this->contextinstanceid));
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
		if (isset($this->other['userid']))
			$userid="&userid={$this->other['userid']}";
		else
			$userid='';
		if (isset($this->other['urlaction']))
			$action="&action={$this->other['urlaction']}";
		else
			$action='';
        return array($this->courseid, 'attendanceregister', $this->other['action'],
            'view.php?a=' . $this->objectid . $userid . $action, $this->objectid, $this->contextinstanceid);
    }
}