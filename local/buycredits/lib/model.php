<?php

defined('INTERNAL_ACCESS') or die;

abstract class gds_credit_model
{
    protected $gdscredit;
    protected $db;
   
    public function __construct(gds_credit $base)
    {
        global $DB;
        
        $this->db = $DB;
        $this->gdscredit = $base;
    }

    /**
     * Load a model class
     *
     * @param string $name
     * @return gds_credit_model
     */
    protected function model($name)
    {
        return $this->gdscredit->model($name);
    }
}