<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Minitimeheure extends CI_Controller {
    public function __construct() {
        parent::__construct();

        $this->load->library('ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->helper('url');

        if (! $this->ion_auth->logged_in() &&
              $this->uri->segment(2) !== 'login')
            redirect('/minitimeheure/login', 'refresh');
        else if (! $this->ion_auth->in_group('user') &&
              $this->uri->segment(2) !== 'login')
            redirect('/minitimeheure/login', 'refresh');
    }

    public function index() {
        $this->load->view('minitimeheure/timeheure');
    }

    public function login() {
        $data = Array();

        //validate form input
        $this->form_validation->set_rules('identity', 'identity', 'required');
        $this->form_validation->set_rules('password', 'password', 'required');

        if ($this->form_validation->run() == true) { //check to see if the user is logging in
            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), FALSE)) {
                //if the login is successful
                //redirect them back to the home page

                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('/minitimeheure', 'refresh');
            } else {
                //if the login was un-successful
                //redirect them back to the login page

                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('/minitimeheure/login', 'refresh');
            }
        } else {
            //the user is not logging in so display the login page
            //set the flash data error message if there is one

            $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('identity'),
                'autofocus' => 'autofocus',
                'placeholder' => 'Utilisateur'
            );
            $data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'placeholder' => 'Mot de passe'
            );

            $this->load->view('minitimeheure/login', $data);
        }
    }

    public function logout() {
        $this->ion_auth->logout();

        $this->session->set_flashdata('message', 'Vous vous êtes déconnecté !');
        redirect('/minitimeheure/login', 'refresh');
    }

    ////////////////////////////////////////////////////////////////////////////
    // Liste des projets

    public function get_projets() {
        $this->load->model('gestion_projet');
        $data = $this->gestion_projet->get_my_projet();

        // Fin, affichage des résultats

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }
    public function get_dues() {
        $this->load->model('gestion_projet');
        $data = $this->gestion_projet->get_my_dues();

        // Fin, affichage des résultats

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }
    public function get_infoprojet($id_projet = FALSE) {
        $this->load->model('gestion_projet');
        $this->load->model('gestion_timer');

        $data = array();

        // Remplir data avec les valeurs utiles des projets

        $tempstotal = $this->gestion_timer->temps_par_projet($id_projet);

        $projet = $this->gestion_projet->get_one_projet($id_projet);
        $dureeprojet = $projet['duree_projet'] * 60;

        $data['id_projet'] = $id_projet;
        $data['temps_total'] = $tempstotal;
        $data['temps_prevu'] = $dureeprojet;

        // Fin, affichage des résultats

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }

    ////////////////////////////////////////////////////////////////////////////
    // Lancement et arrêt des timers

    public function get_timer($id_projet = FALSE) {
        $data = array();

        $id = $this->ion_auth->user()->row()->id;
        if ($id_projet) {
            $date = date('Y-m-d H:i:s');

            $this->db->insert('timers', array(
                'id_projet' => $id_projet,
                'id' => $id,
                'demarrage' => $date,
                'arret' => $date,
                'notes_temps' => '#StartedAndNeverStopped#'
            ));

            $data['id_timer'] = $this->db->insert_id();
        }
        else
            $data['id_timer'] = -1;

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }
    public function set_timer($id_timer = FALSE) {
        $data = array();

        $date = $this->input->post('duree');
        $note = $this->input->post('note');

        $data['erreur'] = '';

        if($id_timer && ! preg_match('/^\d+$/', $id_timer))
            $data['erreur'] .= ($data['erreur'] === '' ? ", " : "") . "'id_timer' doit etre un nombre entier";

        if($date && ! preg_match('/^\d+$/', $date))
            $data['erreur'] .= ($data['erreur'] === '' ? ", " : "") . "'duree' doit etre un nombre entier";


        if (preg_match('/^\d+$/', $id_timer) && preg_match('/^\d+$/', $date)) {
            $depart = $this->db->where('id_timer', $id_timer)->get('timers')->row()->demarrage;

            // Filtrage : Pas de timer de moins de 15s.

            if((int) $date > 15)
                $this->db->where('id_timer', $id_timer)->update('timers', array('arret' => date('Y-m-d H:i:s', strtotime($depart) + $date), 'notes_temps' => $note));
            else {
                $this->db->where('id_timer', $id_timer)->delete('timers');
                $data['recept'] = TRUE;
            }

            if ($this->db->affected_rows() > 0)
                $data['recept'] = TRUE;
            else {
                $data['recept'] = FALSE;
                $data['erreur'] .= ($data['erreur'] === '' ? ", " : "") . "Base de donnée non affectee : " . $this->db->error_message();
            }
        }
        else
            $data['recept'] = FALSE;

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }

    ////////////////////////////////////////////////////////////////////////////
    // Liste de favoris

    public function get_favoris() {
        $this->load->model('gestion_favoris');
        $data = $this->gestion_favoris->get_favoris();

        // Fin, affichage des résultats

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }
    public function set_favoris($id_projet) {
        $this->load->model('gestion_favoris');
        $this->gestion_favoris->set_favoris($id_projet);

        // Fin, renvoie l'ensemble des favoris
        $data = $this->gestion_favoris->get_favoris();

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }
    public function unset_favoris($id_projet) {
        $this->load->model('gestion_favoris');
        $this->gestion_favoris->unset_favoris($id_projet);

        // Fin, renvoie l'ensemble des favoris
        $data = $this->gestion_favoris->get_favoris();

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($data));
    }

    ////////////////////////////////////////////////////////////////////////////
    // Chargement des contenus

    public function get_content($content = 'Projet') {
        $this->load->view('minitimeheure/content' . $content);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Chargement des contenus

    public function unset_participation($id_projet) {
        $this->load->model('gestion_projet');
        $this->gestion_projet->unset_participation($id_projet, $this->ion_auth->user()->row()->id);

        $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('success' => 'true')));
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */