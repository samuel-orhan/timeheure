<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Gestion_favoris extends CI_Model {
    public function __construct() {
        parent::__construct();

        $this->load->library('ion_auth');
        $this->load->library('session');
    }

    public function get_favoris() {
        $user = $this->ion_auth->user()->row();

        $this->db->select('clients.nom_client, clients.index_client, projets.*');
        $this->db->distinct();

        $this->db->join('clients', 'projets.id_client = clients.id_client');
        $this->db->join('users', 'projets.id = users.id');
        $this->db->join('favoris', 'favoris.id_projet = projets.id_projet');
        $this->db->join('participants', 'projets.id_projet = participants.id_projet');

        $this->db->where(array('participants.id' => $user->id, 'etat_projet LIKE' => 'actif'));
        $this->db->order_by('clients.index_client', 'asc');

        $query = $this->db->get('projets');

        // Valeur de retour

        $data = array();
        foreach ($query->result() as $row) {
            $projet = array();

            foreach($row as $k => $v)
                $projet[$k] = $v;

            $data[] = $projet;
        }
        $query->free_result();

        // FIN

        return $data;
    }

    public function set_favoris($id_projet) {
        $user = $this->ion_auth->user()->row();

        // Vérifie la validité du projet

        $this->db->select('id_projet');
        $this->db->where(array('participants.id' => $user->id, 'id_projet' => $id_projet));
        $query = $this->db->get('participants');

        if($query->num_rows() > 0) {
            // On peut insérer le projet

            $data = array(
                'id_projet' => $id_projet,
                'user' => $user = $this->ion_auth->user()->row()->id
            );

            $this->db->insert('favoris', $data);
            ; // TODO: Finir l'insert de favoris

        }
        $query->free_result();
    }

    public function unset_favoris($id_projet) {
        $this->db->where(array('id_projet' => $id_projet, 'user' => $this->ion_auth->user()->row()->id));
        $this->db->delete('favoris');
    }
}