<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Front extends Public_Controller
{

	public $directory = '';
	public $data_file = '';

	public function __construct()
    {

        parent::__construct();

		// Load libraries
		$this->lang->load('documentation');
		$this->load->model('documentation_m');
		$this->load->helper('markdown');

		// Create data object
		$this->data = new stdClass();

		// Get current directory information and data
		$this->directory = $this->documentation_m->get_directory();
		$this->data_file = $this->directory.'_data';
		$this->_data     = json_decode(file_get_contents($this->data_file), true) or array();

    }

    public function index()
    {

    	// Variables
    	$document = array();
        $pages    = func_get_args();

   		// Get requested page
    	if( is_array($pages) AND !empty($pages) )
    	{

    		// Get last page
    		$page = end($pages);
    		
    		// Loop documents
    		foreach( $this->_data AS $doc )
    		{
    			if( $doc['slug'] == $page )
    			{
    				$document = $doc;
    				break;
    			}
    		}

    	}
    	else
    	{
    		$document = $this->_data[0];
    	}

    	// Check we have a page
    	if( !empty($document) )
    	{

    		// Assign data
            $doc_tree               = $this->documentation_m->generate_doc_tree($this->_data);
            $doc_tree               = $doc_tree[0]['children'];
    		$this->data->navigation = $this->documentation_m->build_navigation($doc_tree, $pages);
    		$this->data->content    = Markdown(file_get_contents($this->directory.$document['slug'].'.md'));

    		// Build breadcrumbs
    		$this->template->set_breadcrumb('Documentation', 'documentation');
    		if( is_array($pages) AND !empty($pages) )
            {
                $this->documentation_m->build_breadcrumbs($document, $this->_data, $this->template);
            }

   			// Build the page
			$this->template->title($document['title'])
						   ->build('index', $this->data);
    	}
    	else
    	{
			set_status_header(404);
			echo Modules::run('pages/_remap', '404');
    	}

    }

}