<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Transfert extends QM_Controller {
    var $authorisation = array('superadmin');

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        // Transfert de l'ancienne BDD 'job' vers les nouvelles données 'quinzemille'

        if(! $this->db->table_exists('old_client') ||
                ! $this->db->table_exists('old_users') ||
                ! $this->db->table_exists('old_projet') ||
                ! $this->db->table_exists('old_timer') ||
                ! $this->db->table_exists('old_participation')) {

            $this->session->set_flashdata('message', "Aucun transfert effectué, il manque d'anciennes tables");
            redirect('/index', 'refresh');
        }

        /*
        *   Je commence par la table client
        */

        $this->db->truncate('clients');

        $transfertClients = array();
        $query = $this->db->get('old_client');

        foreach($query->result() as $row) {
            $data = array(
                'nom_client' => $row->nom,
                'index_client' => count($transfertClients)
            );
            $this->db->insert('clients', $data);

            $transfertClients[$row->id_client] = $this->db->insert_id();
        }
        $query->free_result();

        /*
        *   Construction d'un tableau de conversion pour les identifiants
        */

        $transfertUsers = array();

        $query = $this->db->get('old_users');
        foreach($query->result() as $row) {
            $transfertUsers[$row->id] = $this->db->where('username', $row->username)->get('users')->row()->id;
        }
        $query->free_result();

        /*
         *  Reconstruction des tables projets
         */

        $this->db->truncate('projets');
        $user = $this->ion_auth->user()->row();

        $transfertProjets = array();

        $query = $this->db->get('old_projet');
        foreach($query->result() as $row) {
            $duree_projet = $row->duree;

            if(isset($transfertClients[$row->id_client])) {
                if(file_exists('http://192.168.1.239' . $row->fichierbdc))
                    copy('http://192.168.1.239' . $row->fichierbdc, './bdc/' . str_replace('/job/bdc/', '', $row->fichierbdc));

                $data = array(
                    'id' => $user->id,

                    'id_client' => $transfertClients[$row->id_client],
                    'date_creation' => date('Y-m-d', strtotime($row->date)),
                    'bdc_projet' => $row->bdc,
                    'dossier_projet' => $row->dossier,
                    'description_projet' => $row->description,
                    'devis_projet' => $row->devis,
                    'facture_projet' => $row->facture,
                    'fichier_bdc_projet' => ($row->fichierbdc ? '/bdc/' . str_replace('/job/bdc/', '', $row->fichierbdc) : NULL),
                    'nom_projet' => $row->nom,
                    'etat_projet' =>($row->etat === 'termine' ? 'pause' : 'actif'),
                    'duree_projet' => $duree_projet
                );
                $this->db->insert('projets', $data);

                $transfertProjets[$row->id_projet] = $this->db->insert_id();
            }
        }
        $query->free_result();

        /*
         *  Reconstruction des timers
         */

        $this->db->truncate('timers');
        $this->db->truncate('participants');

        $query = $this->db->get('old_timer');
        foreach($query->result() as $row) {
            $participation = $this->db->select('id, id_projet')->distinct()->where('id_participation', $row->id_participation)->get('old_participation');

            foreach($participation->result() as $part) {
                if(isset($transfertProjets[$part->id_projet]) && isset($transfertUsers[$part->id])) {
                    $data = array(
                        'id_projet' => $transfertProjets[$part->id_projet],
                        'id' => $transfertUsers[$part->id],
                        'demarrage' => $row->demarrage,
                        'arret' => $row->arret,
                        'notes_temps' => $row->Notes
                    );

                    $this->db->insert('timers', $data);
                    $this->db->insert('participants', array('id' => $transfertUsers[$part->id], 'id_projet' => $transfertProjets[$part->id_projet]));
                }
            }
            $participation->free_result();
        }
        $query->free_result();

        // Unicité de la BDD Participation

        $unique = $this->db->select('id, id_projet')->distinct()->get('participants');
        foreach($unique->result() as $row) {
            $id = $row->id;
            $id_projet = $row->id_projet;

            $this->db->where(array('id' => $id, 'id_projet' => $id_projet))->delete('participants');
            $this->db->insert('participants', array('id' => $id, 'id_projet' => $id_projet));
        }

        /*
         *  Fin
         */

        $this->session->set_flashdata('message', "Transfert des données terminé");
        redirect('/index', 'refresh');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */