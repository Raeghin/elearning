<?php
require_once('/../../../config.php');

if (isset($_POST['assignUserFunc'])) {
    $userid = $_POST['userid'];
	$enddate = $_POST['enddate'];
	$courseid = $_POST['courseid'];
	$creatorid = $_POST['creatorid'];

	$user = new assignuserclass;

	echo $user->enroluser($userid, $courseid, $enddate, $creatorid);
}

class assignuserclass
{
	public function __construct()
	{
		global $DB;
		$this->db = $DB;
	}

	public function enroluser($userid, $courseid, $enddate, $creatorid)
	{
        $duration = $this->getcourseduration($courseid);
        $costs = $this->getcoursecosts($courseid);

        $credits = 0;
        $field = $this->db->get_record('local_usercredits', array('customer_id' => $creatorid));
        if($field != false)
            $credits = $field->amount;

        //if($credits < $costs)
        //    return 'notenoughcredits';

        $startdate = new DateTime($enddate);
        $startdate->modify('-' . ($duration - 1) . ' days');

        $lastdate = new DateTime($enddate);
        $lastdate->modify('+1 days');

        $enrolment = enrol_get_plugin("manual");

        $instance = $this->get_instance_for_course($courseid);
        $enrol = $enrolment->enrol_user($instance, $userid, $instance->roleid,$startdate->getTimestamp(), $lastdate->getTimestamp());

        $field = $this->db->get_record('local_usercredits', array('customer_id' => $creatorid));

        $record = new stdClass();
        $record->id = $field->id;
        $record->customer_id = $creatorid;
        $record->amount = $field->amount - $costs;

        $this->db->update_record('local_usercredits', $record);

        return $record->amount;
    }

	public function getcoursecosts($courseid)
	{
		$field = $this->db->get_record('local_courserequirements', array('course_courseid' => $courseid));
		return $field->credits;
	}

	public function getcourseduration($courseid)
	{
		$field = $this->db->get_record('local_courserequirements', array('course_courseid' => $courseid));
        return $field->days;
	}

	public function checkifuseralreadyenrolled($userid, $enddate, $courseid)
	{
		$sql = "SELECT COUNT(e.id) FROM {user_enrolments} ue JOIN {enrol} e ON ue.enrolid = e.id WHERE e.courseid = " . $courseid . " AND ue.userid = " . $userid . "	AND ((ue.timestart < " . date_format($enddate, 'U') ." AND ue.timeend < " . date_format($enddate, 'U') . ") OR ue.timeend = 0)";
		if($this->db->count_records_sql($sql) == 0)
			return false;
		else
			return true;
	}

	private function getenrolid($courseid)
	{
		$field = $this->db->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'));
		return $field->id;
	}

    /**
     * Get the instance of this plugin attached to a course if any
     * @param int $courseid id of course
     * @param bool $onlyenabled only return an enabled instance
     * @return object|bool $instance or false if not found
     */
    public function get_instance_for_course($courseid, $onlyenabled=true) {
        global $DB;
        $params = array('enrol' => 'manual', 'courseid' => $courseid);
        if (!empty($onlyenabled)) {
            $params['status'] = ENROL_INSTANCE_ENABLED;
        }
        return $DB->get_record('enrol', $params);
    }

}

defined('INTERNAL_ACCESS') or die;

class gds_credit_model_editcourse extends gds_credit_model
{

	public function setcourseid($courseid)
	{
		$this->courseid = $courseid;
	}
	
	public function getcourse() {
		return $this->db->get_record('course', array('id' => $this->courseid));
	}

	public function getactivecourseparticipants($creatorid) {
		
        $sql = "SELECT DISTINCT u.id AS userid, u.username, u.firstname, u.lastname, ue.timestart, ue.timeend
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = :contextlevel
            JOIN {course} c ON c.id = ct.instanceid AND e.courseid = c.id
            JOIN {role} r ON r.id = ra.roleid AND r.shortname = 'student'
            JOIN {local_createdusers} lcu ON lcu.user_userid = u.id

            WHERE u.suspended = 0 AND u.deleted = 0
            AND (ue.timeend = 0 OR ue.timeend > :now) AND ue.status = :active AND lcu.creator_userid = :creatorid AND c.id = :courseid";
        
        $params = array('now'=>round(time(), -2));
        $params['contextlevel'] = CONTEXT_COURSE;
        $params['active']  = ENROL_USER_ACTIVE;
        $params['creatorid']  = $creatorid;
        $params['courseid'] = $this->courseid;

        $users = $this->db->get_records_sql($sql, $params);

		return $users;
	}

	public function getuserdetails($userid)
	{
		return $this->db->get_record('user', array('id' => $userid));
	}

	public function getcreatedusers($creatorid)
	{
		$values = $this->db->get_records('local_createdusers', array('creator_userid' => $creatorid));

        $users = array();
        foreach ($values as $value) {
            $user = $this->getuserdetails($value->user_userid);

            #$participants = $this->getcourseparticipants($creatorid, $this->courseid)

            array_push($users, $user);

        }

		return $users;
	}

	public function checkifuseralreadyenrolled($user, $activeparticipants)
	{
		foreach($activeparticipants AS $activeuser)
            if($activeuser->userid == $user->id)
                return true;

        return false;
	}
}