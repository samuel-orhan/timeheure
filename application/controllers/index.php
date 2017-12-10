<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Index extends QM_Controller {
    var $authorisation = array('user', 'superuser', 'admin', 'superadmin');

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $user = $this->ion_auth->user()->row();
       
        // AFFICHAGE PAGE
        
        $data = array();

        $data['title'] = 'Administration de vos timers';
        $data['username'] = $user->username;
        $data['message'] = $this->session->flashdata('message');
        
        $presence = $this->db->where(array('user' => $user->id, 'date' => date('Y-m-d')))->get('presence')->row();
        if($presence) {
            $data['matin_debut'] = $presence->matin_debut;
            $data['matin_fin'] = $presence->matin_fin;
            $data['am_debut'] = $presence->am_debut;
            $data['am_fin'] = $presence->am_fin;
        }
        else {
            $data['matin_debut'] = NULL;
            $data['matin_fin'] = NULL;
            $data['am_debut'] = NULL;
            $data['am_fin'] = NULL;  
        }
        
        $this->view('index', $data);
    }
   
    public function presence($tranche, $heure) {
        $user = $this->ion_auth->user()->row();
        
        if($tranche && $heure && preg_match('/^\d+:\d{2}$/', urldecode($heure))) {
            $data = array();
            $data[$tranche] = urldecode($heure);
            $data['user'] = $user->id;
            $data['date'] = date('Y-m-d');
            
            if($this->db->where(array('user' => $user->id, 'date' => date('Y-m-d')))->get('presence')->num_rows() > 0)
                $this->db->where(array('user' => $user->id, 'date' => date('Y-m-d')))->update('presence', $data);
            else
                $this->db->insert('presence', $data);
        }
        
        if($this->db->affected_rows() > 0)
            $this->session->set_flashdata('message', "Votre horaire à été modifié");
        else
            $this->session->set_flashdata('message', "Impossible de modifier votre horaire");
        
        redirect("/index", 'refresh');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */