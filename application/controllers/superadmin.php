<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Superadmin extends QM_Controller {
    var $authorisation = array('superadmin');

    public function __construct() {
        parent::__construct();
    }

    /*
     * Pas de fonction d'Index (géré par la super classe)
     */

    public function register_user() {
        /*
         * Création d'un utilisateur
         */

        $data = array();
        $data['title'] = "Ajout d'un utilisateur";

        $this->form_validation->set_rules('username', 'Nom d\'utilisateur', 'required');

        $this->form_validation->set_rules('first_name', 'Nom', 'required');
        $this->form_validation->set_rules('last_name', 'Prénom', 'required');
        $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Mot de passe', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');

        $this->form_validation->set_rules('group[]', 'Groupe(s) membre(s)', 'required');

        if ($this->form_validation->run() == true) {
            $username = $this->input->post('username');
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
            );

            $group = $this->input->post('group');
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $group)) {
            //check to see if we are creating the user
            //redirect them back to the admin page

            $this->session->set_flashdata('message', "Utilisateur créé");
            redirect("/superadmin/showusers", 'refresh');
        } else {
            //display the create user form
            //set the flash data error message if there is one

            $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

            $data['username'] = array('name' => 'username',
                'id' => 'username',
                'type' => 'text',
                'value' => $this->input->post('username') ? $this->input->post('username') : '',
            );
            $data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'value' => $this->form_validation->set_value('password'),
            );

            $data['first_name'] = array('name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'value' => $this->input->post('first_name') ? $this->input->post('first_name') : '',
            );
            $data['last_name'] = array('name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'value' => $this->input->post('last_name') ? $this->input->post('last_name') : '',
            );
            $data['email'] = array('name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'value' => $this->input->post('email') ? $this->input->post('email') : '',
            );

            $groups = $this->ion_auth->groups();

            $data['role'] = array();
            foreach($groups->result() as $group) {
                $data['role'][$group->id] = $group->description;
            }
            $data['group_selected'] = array();
            if($this->input->post('group'))
                $data['group_selected'] = $this->input->post('group');
            
            $this->view('superadmin/updateuser', $data);
        }
    }

    public function update_user($id = FALSE) {
        $data = array();
        $data['title'] = "Modification d'un utilisateur ";

        $user = NULL;
        if ($id && preg_match('/^\d+$/', $id))
            $user = $this->ion_auth->user($id)->row();
        
        if(! $user) {
            $this->session->set_flashdata('message', "Utilisateur non identifié");
            redirect("/superadmin/get_users", 'refresh');
        }

        /*
         * Création d'un utilisateur
         */

        $this->form_validation->set_rules('username', 'Nom d\'utilisateur', 'required');

        $this->form_validation->set_rules('first_name', 'Nom', 'required');
        $this->form_validation->set_rules('last_name', 'Prénom', 'required');
        $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Mot de passe', 'min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');

        $this->form_validation->set_rules('group[]', 'Groupe(s) membre(s)', 'required');

        if ($this->form_validation->run() == true) {
            $update = array();

            $update['username'] = $this->input->post('username');
            $update['email'] = $this->input->post('email');

            if($this->input->post('password') != FALSE)
                $update['password'] = $this->input->post('password');

            $update['first_name'] = $this->input->post('first_name');
            $update['last_name'] = $this->input->post('last_name');

            $this->update_group($user->id, $this->input->post('group'));

            if ($this->form_validation->run() == true && $this->ion_auth->update($user->id, $update)) {
                //check to see if we are creating the user
                //redirect them back to the admin page

                $this->session->set_flashdata('message', "Utilisateur modifié");
                redirect("/superadmin/showusers", 'refresh');
            }
        }
        else {
            //display the create user form
            //set the flash data error message if there is one

            $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

            $data['username'] = array('name' => 'username',
                'id' => 'username',
                'type' => 'text',
                'value' => $this->input->post('username') ? $this->input->post('username') : $user->username,
            );
            $data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'value' => $this->form_validation->set_value('password'),
            );

            $data['first_name'] = array('name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'value' => $this->input->post('first_name') ? $this->input->post('first_name') : $user->first_name,
            );
            $data['last_name'] = array('name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'value' => $this->input->post('last_name') ? $this->input->post('last_name') : $user->last_name,
            );
            $data['email'] = array('name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'value' => $this->input->post('email') ? $this->input->post('email') : $user->email,
            );

            $groups = $this->ion_auth->groups()->result();

            $data['role'] = array();
            foreach($groups as $group) {
                $data['role'][$group->id] = $group->description;
            }

            $data['group_selected'] = array();
            
            if($this->input->post('group'))
                $data['group_selected'] = $this->input->post('group');
            else {
                $groups = $this->ion_auth->get_users_groups($user->id)->result();
                foreach ($groups as $group)
                    $data['group_selected'][] = $group->id;
            }
            
            $this->view('superadmin/updateuser', $data);
        }
    }

    public function showusers() {
        //list the users

        $data = array('users' => array());
        
        $users = $this->db->get('users');
        foreach ($users->result() as $user) {
            $groups = '';
            foreach($this->ion_auth->get_users_groups($user->id)->result() as $group)
                $groups .= ($groups === '' ? $group->description : ', ' . $group->description);
                
            $userdata = array(
                'id' => $user->id,
                'username' => $user->username,
                'groups' => $groups
            );
            
            $data['users'][] = $userdata;
        }

        $data['message'] = $this->session->flashdata('message');

        $this->view('superadmin/showusers', $data);
    }

    private function update_group($user_id, $groups) {
        $this->db->query("DELETE FROM `users_groups` WHERE `user_id`=" . $user_id);

        foreach($groups as $group_id)
            $this->db->query("INSERT INTO `users_groups`(`user_id`, `group_id`) VALUES (". $user_id . "," . $group_id . ")");
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */