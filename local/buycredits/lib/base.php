<?php

define('INTERNAL_ACCESS', 1);

class gds_credit
{
    protected $user;
    protected $config;
    
    public function __construct(array $configs = null)
    {
        if ($configs !== null) {
            $this->set_configs($configs);
        }
    }

    public function set_configs(array $configs)
    {
        foreach ($configs as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }
        return $this;
    }
	
	/**
     * Load a controller
     *
     * @param string $request
     * @return credits_controller
     */
    public function controller($request, $modelname)
    {
        $requestparts = explode('/', $request);

        // load controller abstract and find the requested controller
        $this->load_file('lib/controller.php');
        if (!$this->load_file('controllers/' . $requestparts[0] . '.php', true)) {
            print_error('invalidpage');
        }

        // controller class name
        $class = 'gds_credit_controller_' . $requestparts[0];

        // setup action call
        if (isset($requestparts[1])) {
            $action = $requestparts[1];
        } else {
            $action = 'index';
            $request = $requestparts[0] . '/index';
        }

        // return the controller
        return new $class($action, $request, $this, $modelname);
    }
	
	/**
     * Load a file
     *
     * @param string $file
     * @param boolean $disableerror
     * @return mix
     */
    public function load_file($file, $disableerror = false)
    {
        $filepath = $this->get_basedir($file);
        if (!file_exists($filepath)) {
            if ($disableerror) {
                return false;
            }
            print_error('invalidfiletoload');
        }
        include_once $filepath;
        return true;
    }
	
	/**
     * Get language string from plugin specific lang dir
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    public function get_string($name, $a = null)
    {
        return stripslashes(get_string($name, 'local_buycredits', $a));
    }
	
	 /**
     * Get plugin base dir
     *
     * @param string $file
     * @return string
     */
    public function get_basedir($file = null)
    {
        return $this->config->dirroot . '/local/buycredits/' . $file;
    }
	
	public function get_user()
    {
        return $this->user;
    }

    public function get_config($name = null)
    {
        if ($name !== null && isset($this->config->{$name})) {
            return $this->config->{$name};
        }
        return $this->config;
    }
	
	/**
     * Load a model class
     *
     * @param string $name
     * @return moo_globalmessage_model
     */
    public function model($name)
    {
		$this->load_file('lib/model.php');
        $this->load_file('models/' . $name . '.php');
        $class = 'gds_credit_model_' . $name;
        return new $class($this);
    }
}