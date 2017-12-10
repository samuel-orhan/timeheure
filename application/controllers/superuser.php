<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Superuser extends QM_Controller {
    var $authorisation = array('superuser');

    public function __construct() {
        parent::__construct();
    }

    public function listeclient() {
        $data = array();
        $data['title'] = 'Liste des clients inscrits';
        
        $data['liste_client'] = array();
        $query = $this->db->get('clients');
        foreach($query->result() as $row)
            $data['liste_client'][] = array('id' => $row->id_client, 'nom' => $row->nom_client, 'index' => $row->index_client);
        
        $data['message'] = $this->session->flashdata('message');
        $this->view('superuser/listeclient', $data);
    }
    
    public function modifier($id_client = FALSE) {
        $data = array();

        if ($id_client && preg_match('/^\d+$/', $id_client)) {
            $query = $this->db->where(array('id_client' => $id_client))->order_by("index_client", "asc")->get('clients');
            
            if ($query->num_rows() > 0) {
                $row = $query->row();

                $data['nom'] = $row->nom_client;
                $data['index'] = $row->index_client;
            } else {
                $data['nom'] = 'client inconnu';
                $data['index'] = '1000';
            }

            $data['title'] = 'Modifier le client';
        } else {
            $data['nom'] = '';
            $data['index'] = $this->db->count_all('clients');
            $data['title'] = 'Ajouter un nouveau client';
        }
        $data['message'] = '';

        // Validation du formulaire

        $this->form_validation->set_rules('nom_client', 'nom du client', 'required');
        $this->form_validation->set_rules('index_client', 'ordre d\'apparition', 'required|integer');
        
        if ($this->form_validation->run() == true) {
            $data = array(
                'nom_client' => $this->input->post('nom_client'),
                'index_client' => $this->input->post('index_client')
            );

            if ($id_client && preg_match('/^\d+$/', $id_client))
                $this->db->where('id_client', $id_client)->update('clients', $data);
            else
                $this->db->insert('clients', $data);

            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('message', "Carnet client modifié");
                redirect("/superuser/listeclient", 'refresh');
            } else {
                $this->session->set_flashdata('message', "Impossible de modifier le carnet client");
                redirect("/superuser/listeclient", 'refresh');
            }
        } else {
            $data['message'] = (validation_errors() ? validation_errors() : $this->session->flashdata('message'));
            
            $data['id_client'] = array('name' => 'id_client',
                'id' => 'id_client',
                'type' => 'hidden',
                'value' => ($this->input->post('id_client') ? $this->input->post('id_client') : $id_client)
            );
            $data['nom_client'] = array('name' => 'nom_client',
                'id' => 'nom_client',
                'type' => 'text',
                'value' => ($this->input->post('nom_client') ? $this->input->post('nom_client') : $data['nom'])
            );
            $data['index_client'] = array('name' => 'index_client',
                'id' => 'index_client',
                'type' => 'text',
                'class' => 'integer',
                'value' => ($this->input->post('index_client') ? $this->input->post('index_client') : $data['index'])
            );
        }

        $this->view('superuser/modifierclient', $data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */