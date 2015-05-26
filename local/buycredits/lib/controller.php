 <?php

defined('INTERNAL_ACCESS') or die;

abstract class gds_credit_controller
{
    protected $gdscredit;
    protected $action = 'index';
    protected $request = null;
    protected $view;
	protected $model;
    protected $pagename = '';
    protected $page;
    
    public function __construct($action, $request, gds_credit $base, $modelname)
    { 
        global $PAGE;
        
        $this->page = $PAGE;
        $this->action = $action;
        $this->request = $request; 
        $this->gdscredit = $base;
        $this->gdscredit->load_file('lib/view.php');
		$this->model = $this->gdscredit->model($modelname);
        $this->view = new gds_credit_view($this->gdscredit, $this->model);	
    }

    /**
     * Run controller action and render its view
     * 
     * @return void
     */
    public final function run()
    {
        // check if action exists
        $actionname = $this->action . '_action';
        if (!method_exists($this, $actionname)) {
            print_error('invalidpage');
        }

        $sitecontext = context_system::instance();
        // You do not have the required permission to access this page.
		if (!has_capability('local/buycredits:view', $sitecontext)) {
            print_error('pagepermission');
        }

        // call the action
        $this->{$actionname}();
        // render request view
        $this->view->render($this->request);
    }

    /**
     * Load a file
     * 
     * @param string $file
     * @return mix
     */
    protected function load_file($file)
    {
        return $this->gdscredit->load_file($file);
    }

    /**
     * Load a model class
     *
     * @param string $name
     * @return model
     */
    protected function model($name)
    {
        return $this->gdscredit->model($name);
    }

    /**
     * Get language string from plugin specific lang dir
     *
     * @param string $name
     * @param string|object $a
     * @return string
     */
    protected function get_string($name, $a = null)
    {
        return $this->gdscredit->get_string($name, $a);
    }

    /**
     * Add CSS file to the <head>
     * 
     * @param array $links
     * @return void
     */
    protected function head_link(array $links)
    {
        foreach ($links as $link) {
            $this->page->requires->css($link);
        }
    }

    protected function get_user()
    {
        return $this->gdscredit->get_user();
    }
}
