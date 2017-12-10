<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Logout extends QM_Controller {
    var $authorisation = NULL;

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->ion_auth->logout();
        redirect('/login', 'refresh');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */