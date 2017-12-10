<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Login extends QM_Controller {
    var $authorisation = array();

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->data['title'] = "Login";

        //validate form input
        $this->form_validation->set_rules('identity', 'identity', 'required');
        $this->form_validation->set_rules('password', 'password', 'required');

        if ($this->form_validation->run() == true) { //check to see if the user is logging in
            //check for "remember me"
            $remember = (bool) $this->input->post('remember');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                //if the login is successful
                //redirect them back to the home page

                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('/index', 'refresh');
            } else {
                //if the login was un-successful
                //redirect them back to the login page

                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('/login', 'refresh');
            }
        } else {
            //the user is not logging in so display the login page
            //set the flash data error message if there is one

            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $this->data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('identity'),
                'autofocus' => 'autofocus'
            );
            $this->data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
            );
            $this->data['remember'] = array('name' => 'remember',
                'id' => 'remember',
                'type' => 'checkbox',
                'value' => '1'
            );

            $this->load->view('login/index', $this->data);
        }
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */