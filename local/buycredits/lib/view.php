<?php

defined('INTERNAL_ACCESS') or die;

class gds_credit_view
{
    protected $gdscredit;
    protected $layout = true;
    protected $data = array();
    protected $output;
	protected $model;
    
    public function __construct(gds_credit $base, gds_credit_model $model)
    {
        global $OUTPUT;

        $this->gdscredit = $base;
        $this->output = $OUTPUT;
		$this->model = $model;
    }

    public function disable_layout()
    {
        $this->layout = false;
        return $this;
    }

    public function render($view)
    {
        if ($this->layout) {
            echo $this->output->header();
        }

        if ($this->pageheading != '') {
            echo $this->output->heading($this->pageheading, 2);
        }

        include_once $this->gdscredit->get_basedir('views/' . $view . '.php');

        if ($this->layout) {
            echo $this->output->footer();
        }        
    }

    /**
     * Get language string from plugin specific lang dir
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    protected function get_string($name, $a= null)
    {
        return $this->gdscredit->get_string($name, $a);
    }

    /**
     * Get base url of the mod
     *
     * @param string $path
     * @return string
     */
    public function base_url($path = '', $relative = false)
    {
        if ($relative) {
            return '/local/buycredits/' . $path;
        }
        return $this->get_config()->wwwroot . '/local/buycredits/' . $path;
    }

    /**
     * Get Moodle base url
     * 
     * @param String $path
     * @return string
     */
    public function core_url($path = '')
    {
        return $this->get_config()->wwwroot . '/' . $path;
    }


    protected function get_user()
    {
        return $this->gdscredit->get_user();
    }

    protected function get_config($name = null)
    {
        return $this->gdscredit->get_config($name);
    }
	
	protected function geturl($name)
	{
		return $this->base_url('models/' . $name . '.php');
	}
}
