<?php
require_once('/../../../config.php');


if (isset($_POST['addUserFunc'])) {
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$email = strtolower($_POST['email']);
	$creatorid = $_POST['uid'];
	
	$user = new createuserclass;
	echo $user->createuser($email, $firstname, $lastname, $email, $creatorid);
}

class createuserclass
{
	public function __construct()
    {
        global $DB;
        $this->db = $DB;
    }

	
	private function checkuseremail($email)
	{
		if($this->db->count_records('user', array('email'=>$email)) > 0)
			return true;
		else
			return false;
	}

	private function checkusername($username)
	{
		if($this->db->count_records('user', array('username'=>$username)) > 0)
			return true;
		else
			return false;
	}

	public function createuser($username, $firstname, $lastname, $email, $creatorid)
	{
		if($this->checkuseremail($email))
			return 'email taken';
		if($username != $email)
			if($this->checkusername($username))
				return 'username taken';
		
		$password = $this->generateRandomString(8);
		
		$user = new StdClass();

		$user->auth = 'manual';
		$user->confirmed = 1;
		$user->mnethostid = 1;
		$user->email = $email;
		$user->username = $username;
		$user->password = md5($password);
		$user->lastname = $lastname;
		$user->firstname = $firstname;
		
		$user->id = $this->db->insert_record('user', $user);
		
		$creator = new StdClass();
		$creator->user_userid = $user->id;
		$creator->creator_userid = $creatorid;
		
		$this->db->insert_record('local_createdusers', $creator);
		
		if($user->id > 0)
			return $password;
		else
			return 'Error creating user';
	}

	private function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}


defined('INTERNAL_ACCESS') or die;

class gds_credit_model_assign extends gds_credit_model
{
	
	public function __construct(gds_credit $base) {
		parent::__construct($base);
	
	}
	
	function adduser($userid)
	{
		$record = new stdClass();
		$record->customer_id = $userid;
		$record->amount = '0';
		
		$this->db->insert_record('local_usercredits', $record);
		$this->addhistory($userid, '0');
	}

	
	public function getcredits($userid)
	{
		$field = $this->db->get_record('local_usercredits', array('customer_id' => $userid));
		
		if($field == false)
		{
			$this->adduser($userid);
			return 0;
		}
		else
		{
			return $field->amount;
		}
	}
	
	public function getcreatedusers($creatorid, $amount = 10)
	{
		$records = $this->db->count_records('local_createdusers', array('creator_userid' => $creatorid));		
		$limit = max($records - $amount, 0);
		
		return $this->db->get_records('local_createdusers', array('creator_userid' => $creatorid), $sort='', $fields='*', $limitfrom=$limit, $limitnum=$records);
	}
	
	public function getuserdetails($userid)
	{
		return $this->db->get_record('user', array('id' => $userid)); 
	}

	public function getcourses() {
		$order = 'shortname';
		$fields = 'id, shortname, fullname';
		return $this->db->get_recordset_select('course', null, null, $order, $fields);
	}
}