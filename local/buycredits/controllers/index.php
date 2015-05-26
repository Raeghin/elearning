<?php

defined('INTERNAL_ACCESS') or die;

class gds_credit_controller_index extends gds_credit_controller
{
    protected $pagename = 'gdscreditmanage';
		
	public function index_action()
    {
        $this->model = $this->model('credit');
		
		$this->view->pageheading = $this->get_string('managegdscredits');
		
        $this->head_link(array(
            $this->view->base_url('assets/css/styles.css', true)
        ));
    }
}
