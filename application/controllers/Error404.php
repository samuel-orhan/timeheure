<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Error404 extends QM_Controller {
    var $authorisation = array();

    public function __construct() {
        parent::__construct();
    }

    function index() {
        $this->load->view('error/404');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */