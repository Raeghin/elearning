<?php
require_once('/../../../config.php');



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

	public function getcourseparticipants($creatorid) {
		$createdusers = $this->db->get_records('local_createdusers', array('creator_userid' => $creatorid), $sort='', $fields='user_userid');

		$sql = "SELECT DISTINCT eu1_u.id, eu1_ue.timestart, eu1_ue.timeend FROM {user} eu1_u JOIN {user_enrolments} eu1_ue ON eu1_ue.userid = eu1_u.id JOIN {enrol} eu1_e ON (eu1_e.id = eu1_ue.enrolid AND eu1_e.courseid = ?) WHERE eu1_u.deleted = 0";
		$registeredusers = $this->db->get_records_sql($sql, array(4));

		$ownusers = array();
		foreach ($createdusers as $user) {
			$uid = $user->user_userid;
			foreach ($registeredusers as $ruser) {
				if ($uid == $ruser->id) {
					$user = $this->getuserdetails($user->user_userid);
					$user->timestart = $ruser->timestart;
					$user->timeend = $ruser->timeend;
					array_push($ownusers, $user);
				}
			}
		}

		return $ownusers;
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
}