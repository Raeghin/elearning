<?php
require_once('/../../../config.php');

defined('INTERNAL_ACCESS') or die;

include($this->config->dirroot . '/local/buycredits/lib/MultiSafepay.combined.php');
include($this->config->dirroot . '/local/buycredits/lib/MultiSafepay.config.php');

class gds_credit_model_credit extends gds_credit_model
{
	public $msp;
	
	public function __construct() {
		global $DB;
		$this->db = $DB;

		$this->msp = new MultiSafepay();
		$this->msp->test                         = MSP_TEST_API;
		$this->msp->merchant['account_id']       = MSP_ACCOUNT_ID;
		$this->msp->merchant['site_id']          = MSP_SITE_ID;
		$this->msp->merchant['site_code']        = MSP_SITE_CODE;
	}
	
	public function addcredits($userid, $amount, $transactionid)
    {
        if(!$this->checkifcreditisalreadyadded($transactionid))
		{
			$field = $this->db->get_record('local_usercredits', array('customer_id' => $userid));
			
			$record = new stdClass();
			$record->id = $field->id;
			$record->customer_id = $userid;
			$record->amount = $field->amount + $amount;
			
			$this->db->update_record('local_usercredits', $record);
			$this->addhistory($userid, $amount, $transactionid);
			return $record->amount;
		} else {
			$field = $this->db->get_record('local_usercredits', array('customer_id' => $userid));
			return $field->amount;
		}
    }
	
	public function checkifcreditisalreadyadded($transactionid)
	{
		if($this->db->count_records('local_usercreditshistory', array('transactionid' => $transactionid)) > 0)
			return true;
		else
			return false;
		
	}
	
    public function substractone($userid)
    {
        return $this->addcredits($userid, '-1');
    }

	public function substractcredits($userid, $amount)
	{
		return $this->addcredits($userid, '-' . $amount);
	}

    public function checkhistory($userid)
	{
		return $this->db->get_record('local_usercreditshistory', array('customer_id' => $userid));
	}

	public function addhistory($userid, $amount,$transactionid)
	{
		$record = new stdClass();
		$record->customer_id = $userid;
		$record->amount = $amount;
		$record->dateofpurchase = time();
		$record->transactionid = $transactionid;
		
		$this->db->insert_record('local_usercreditshistory', $record);
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
	
	private function adduser($userid)
	{
		$record = new stdClass();
		$record->customer_id = $userid;
		$record->amount = '0';
		
		$this->db->insert_record('local_usercredits', $record);
		$this->addhistory($userid, '0');
	}
	
	/**
	*	Verkrijgen verschillende betalings opties via MultiSafePay
	*/
	public function getgatewayoptions()
	{
		$gateways = $this->msp->getGateways();

		$gateway_selection ='<select name="gateway">';

		foreach($gateways as $gateway){
			$gateway_selection .= '<option value="'.$gateway['id'].'">'.$gateway['description'].'</option>';
		}

		$gateway_selection .='</select>';
		
		return $gateway_selection;
	}
	
	/**
	*	Verkrijgen verschillende iDeal-betalings opties via MultiSafePay
	*/
	public function getissueroptions()
	{
		$ideal_issuers = $this->msp->getIdealIssuers();
		
		$issuer_selection ='<select name="issuer">';
		$issuer_selection .= '<option value="">Select your bank</option>';

		foreach($ideal_issuers['issuers'] as $issuer){
			$issuer_selection .= '<option value="'.$issuer['code']['VALUE'].'">'.$issuer['description']['VALUE'].'</option>';
		}

		$issuer_selection .='</select>';
		
		return $issuer_selection;
	}
	
	public function getpaymentlink()
	{
		return '/local/buycredits/lib/pay.php';
	}
	
	public function getcredithistorie($userid, $amount)
	{
		$records = $this->db->count_records('local_usercreditshistory', array('customer_id' => $userid));
				
		$limit = max($records - $amount, 0);
		
		
		return $this->db->get_records('local_usercreditshistory', array('customer_id' => $userid), $sort='', $fields='*', $limitfrom=$limit, $limitnum=$records);
	}
}