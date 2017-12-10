<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Gestionprojets extends QM_Controller
{

    var $authorisation = array('user', 'superuser', 'admin', 'superadmin');

    public function __construct()
    {
        parent::__construct();
    }

    public function projet($projettype = FALSE)
    {
        $data = array('projet' => array());
        $data['title'] = 'Gérez vos projets';
        $data['message'] = $this->session->flashdata('message');
        $data['projettype'] = ($projettype != FALSE ? $projettype : 'actif');

        $this->view('gestionprojets/listeprojets', $data);
    }

    public function get_projets($projettype = FALSE)
    {
        $this->load->model('gestion_projet');

        $data = array(
            'aaData' => array()
        );

        ////////////////////////////////////////////////////////////////////////
        // Pagination

        $limitStart = 0;
        $limitLength = 10;

        if ($this->input->get_post('iDisplayStart') !== FALSE && intval($this->input->get_post('iDisplayLength')) !== -1) {
            $limitStart = $this->input->get_post('iDisplayStart');
            $limitLength = $this->input->get_post('iDisplayLength');
        }

        ////////////////////////////////////////////////////////////////////////
        // Trie & recherche

        $sorton = $this->input->get_post('iSortingCols');
        $sortdir = $this->input->get_post('sSortDir_0');

        $search = $this->input->get_post('sSearch');

        ////////////////////////////////////////////////////////////////////////

        $projets = $this->gestion_projet->get_all_projet($limitStart, $limitLength, $sorton, $sortdir, $search, $projettype);

        foreach ($projets['results'] as $row) {
            $action = '';

//      $action .= '<a href="#" onClick="getTime(' . $row['id_projet'] . '); return false;">Détails du temps passé</a><br>';
//      $action .= '<a href="/gestionprojets/modifierprojet/' . $row['id_projet'] . '">modifier ce projet</a>';

            $action = '<div style="width: 140px">';
            $action .= '<a href="/gestionprojets/modifierprojet/' . $row['id_projet'] . '" class="btn green i_create_write" title="Modifier ce projet"></a>';
            $action .= '<a href="#" onClick="getTime(' . $row['id_projet'] . '); return false;" class="btn blue i_timer" title="Détails du temps passé"></a>';

            switch ($projettype) {
                case 'prevu' :
                case 'clot' :
                    $action .= '<a href="/gestionprojets/" onClick="sateStateProject(' . $row['id_projet'] . ', \'actif\'); return false;" class="btn green i_tick" title="Activer le projet"></a>';
                    break;
                case 'actif' :
                    $action .= '<a href="#" onClick="sateStateProject(' . $row['id_projet'] . ', \'clot\'); return false;" class="btn red i_cross" title="Clore le projet"></a>';
                    break;
            }

            $action .= '</div>';

            if (isset($row['fichier_bdc_projet']) && $row['fichier_bdc_projet'] != NULL)
                $action .= '<br><a href="' . $row['fichier_bdc_projet'] . '">Télécharger le bon de commande</a>';

            ////////////////////////////////////////////////////////////////////

            $data['aaData'][] = array(
                $row['date_creation'],
                ($row['devis_projet'] != NULL ? 'Devis : ' . $row['devis_projet'] : 'Aucun devis') . '<br>' . ($row['bdc_projet'] != NULL ? 'BDC : ' . $row['bdc_projet'] : 'Aucun BDC') . '<br>' . ($row['facture_projet'] != NULL ? 'Facture : ' . $row['facture_projet'] : 'Aucune facture'),
                $row['nom_client'],
                '<strong>' . $row['nom_projet'] . '</strong><br>' . $row['dossier_projet'],
                $action
            );
        }

        $data['iTotalRecords'] = $this->db->where('etat_projet', $projettype)->get('projets')->num_rows();
        $data['iTotalDisplayRecords'] = $projets['totalresults'];
        $data['sEcho'] = $this->input->get_post('sEcho');

        $data['iDisplayLength'] = $limitLength;

        // Sortie JSON

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    public function get_temps_projet($id_projet)
    {
        $this->load->model('gestion_timer');

        $row = array();
        $row['temps_projet'] = $this->gestion_timer->temps_par_projet($id_projet);

        $row['temps_personnes'] = '';

        $canViewAllUser = ($this->ion_auth->in_group('superuser') || $this->ion_auth->in_group('superuser') || $this->ion_auth->in_group('superuser'));

        foreach ($this->ion_auth->users()->result() as $user) {
            $temps = -1;

            if ($canViewAllUser)
                $temps = $this->gestion_timer->temps_par_projet($id_projet, $user->username);
            else if ($user->username === $user)
                $temps = $this->gestion_timer->temps_par_projet($id_projet, $user->username);

            if ($temps > 0)
                $row['temps_personnes'] .= ($row['temps_personnes'] !== '' ? '<br>' : '') . $user->username . ' à passé : ' . floor($temps / 3600) . 'H ' . (floor($temps / 60) - floor($temps / 3600) * 60) . 'mn. sur ce projet.';
        }

        $consome = $this->db->select('duree_projet')->where('id_projet', $id_projet)->get('projets')->row()->duree_projet * 60;
        $ratio = ($consome > 0 ? floor($row['temps_projet'] / $consome * 1000) / 10 : 0);

        if ($ratio > 100)
            $row['temps_personnes'] .= '<br><br>Vous avez dépassé de ' . $ratio . '% le temps dédié au projet.';
        elseif ($ratio > 0)
            $row['temps_personnes'] .= '<br><br>Vous avez consommé ' . $ratio . '% du temps dédié au projet.';
        else
            $row['temps_personnes'] .= '<br><br>Pas de limites de temps sur ce projet.';

        // Sortie JSON

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($row));
    }

    public function modifierprojet($id_projet = FALSE)
    {
        $this->load->model('gestion_projet');

        $data = array();
        $projet = $this->gestion_projet->get_one_projet($id_projet);
        $data['nom'] = '';

        $id_user = $this->ion_auth->user()->row()->id;

        if ($id_projet && preg_match('/^\d+$/', $id_projet)) {
            $data['nom'] = $projet['nom_projet'];
            $data['title'] = 'Modifier le projet';
        } else
            $data['title'] = 'Ajouter un nouveau projet';

        $data['message'] = '';


        // Validation du formulaire

        $this->form_validation->set_rules('nom_projet', 'nom du projet', 'required');
        $this->form_validation->set_rules('description_projet', 'description du projet', '');

        $this->form_validation->set_rules('id_client', 'client', 'required|integer');

        $this->form_validation->set_rules('bdc_projet', 'bon de commande', 'max_length[30]');
        $this->form_validation->set_rules('devis_projet', 'devis', 'max_length[30]');
        $this->form_validation->set_rules('facture_projet', 'facture', 'max_length[30]');


        $this->form_validation->set_rules('etat_projet', 'état du projet', 'required');
        $this->form_validation->set_rules('users[]', 'utilisateurs', 'integer');

        $this->form_validation->set_rules('duree_projet', 'estimation de temps', '');

        $this->form_validation->set_rules('duree_heures', 'estimation de temps (heures)', 'integer');
        $this->form_validation->set_rules('duree_minutes', 'estimation de temps (minutes)', 'max_length[2]|integer');

        $this->form_validation->set_rules('dead_line', 'date de fin du projet', '');

        if ($this->form_validation->run() == true) {
            // Upload d'un fichier

            $up = $this->do_upload();
            $filename = FALSE;
            if ($up['error'] == NULL)
                $filename = '/bdc/' . $up['data']['file_name'];

            $data = array(
                'id_client' => $this->input->post('id_client'),
                'id' => $id_user,
                'date_creation' => ($id_projet ? $projet['date_creation'] : date('Y-m-d')),
                'nom_projet' => $this->input->post('nom_projet'),
                'etat_projet' => $this->input->post('etat_projet'),
                'description_projet' => $this->input->post('description_projet'),
                'bdc_projet' => $this->input->post('bdc_projet'),
                'devis_projet' => $this->input->post('devis_projet'),
                'facture_projet' => $this->input->post('facture_projet'),
                'duree_projet' => ((int)$this->input->post('duree_heures') * 60 + (int)$this->input->post('duree_minutes')),
                'dead_line' => $this->input->post('dead_line')
            );

            $participants = $this->input->post('users');
            if ($this->input->post('users'))
                $participants = array_unique($this->input->post('users'));

            // Si un fichier à été téléchargé

            if ($filename)
                $data['fichier_bdc_projet'] = $filename;

            // Saisie du projet

            if ($this->gestion_projet->set_projet($id_projet, $data, $participants) > 0) {
                $this->session->set_flashdata('message', "Liste des projets modifiée" . ($up['error'] != NULL ? ' : ' . $up['error'] : ''));
                redirect("/gestionprojets/projet", 'refresh');
            } else {
                $this->session->set_flashdata('message', "Impossible de modifier la liste des projets" . ($up['error'] != NULL ? ' : ' . $up['error'] : ''));
                redirect("/gestionprojets/projet", 'refresh');
            }
        } else {
            $data['message'] = (validation_errors() ? validation_errors() : $this->session->flashdata('message'));

            $data['id_projet'] = array('name' => 'id_projet',
                'id' => 'id_projet',
                'type' => 'hidden',
                'value' => ($this->input->post('id_projet') ? $this->input->post('id_projet') : ($id_projet ? $id_projet : NULL))
            );

            // Recherche des client

            $clients = $this->db->select('id_client, nom_client')->order_by('index_client', 'asc')->get('clients');
            $data['clients'] = array();
            foreach ($clients->result() as $row)
                $data['clients'][$row->id_client] = $row->nom_client;

            $data['client_selected'] = ($this->input->post('id_client') ? $this->input->post('id_client') : ($id_projet ? $projet['id_client'] : 0));

            // Recherche des utilisateurs à associer

            $data['users'] = array();
            $users = $this->db->select('users.id, users.username')->join('users_groups', 'users_groups.user_id = users.id')->where('users_groups.group_id', '4')->get('users');
            foreach ($users->result() as $user)
                $data['users'][$user->id] = $user->username;

            $data['selected_users'] = array();
            if ($id_projet) {
                $selected = $this->db->select('id')->where('id_projet', $id_projet)->get('participants');

                foreach ($selected->result() as $id)
                    $data['selected_users'][] = $id->id;
            } else if ($this->input->post('users'))
                $data['selected_users'] = $this->input->post('users');

            if (!$this->ion_auth->in_group('superuser') &&
                !$this->ion_auth->in_group('admin') &&
                !$this->ion_auth->in_group('superadmin')) {

                $data['id_users'] = $this->ion_auth->user()->row()->id;
                $data['username'] = $this->ion_auth->user()->row()->username;
            }

            // Poursuite sur les item du projet

            $data['nom_projet'] = array('name' => 'nom_projet',
                'id' => 'nom_projet',
                'type' => 'text',
                'value' => ($this->input->post('nom_projet') ? $this->input->post('nom_projet') : ($id_projet ? $projet['nom_projet'] : ''))
            );

            $data['bdc_projet'] = array('name' => 'bdc_projet',
                'id' => 'bdc_projet',
                'type' => 'text',
                'maxlength' => 30,
                'value' => ($this->input->post('bdc_projet') ? $this->input->post('bdc_projet') : ($id_projet ? $projet['bdc_projet'] : ''))
            );
            $data['devis_projet'] = array('name' => 'devis_projet',
                'id' => 'devis_projet',
                'type' => 'text',
                'maxlength' => 30,
                'value' => ($this->input->post('devis_projet') ? $this->input->post('devis_projet') : ($id_projet ? $projet['devis_projet'] : ''))
            );
            $data['facture_projet'] = array('name' => 'facture_projet',
                'id' => 'facture_projet',
                'type' => 'text',
                'maxlength' => 30,
                'value' => ($this->input->post('facture_projet') ? $this->input->post('facture_projet') : ($id_projet ? $projet['facture_projet'] : ''))
            );

            $data['description_projet'] = array('name' => 'description_projet',
                'id' => 'facture_projet',
                'value' => ($this->input->post('facture_pdescription_projetrojet') ? $this->input->post('description_projet') : ($id_projet ? $projet['description_projet'] : ''))
            );

            // Durée programmé

            $data['duree_heures'] = array('name' => 'duree_heures',
                'id' => 'duree_heures',
                'type' => 'number',
                'class' => 'integer',
                'data-min' => '0',
                'value' => ($this->input->post('duree_duree_heuresprojet') ? $this->input->post('duree_heures') : ($id_projet ? floor($projet['duree_projet'] / 60) : ''))
            );
            $data['duree_minutes'] = array('name' => 'duree_minutes',
                'id' => 'duree_minutes',
                'type' => 'number',
                'class' => 'integer',
                'data-min' => '0',
                'data-max' => '60',
                'data-step' => '15',
                'value' => ($this->input->post('duree_minutes') ? $this->input->post('duree_minutes') : ($id_projet ? ($projet['duree_projet'] % 60) : ''))
            );

            $data['dead_line'] = array('name' => 'dead_line',
                'id' => 'dead_line',
                'type' => 'text',
                'class' => 'date',
                'value' => ($this->input->post('dead_line') ? $this->input->post('dead_line') : ($id_projet ? $projet['dead_line'] : ''))
            );

            // Reste le fichier 'fichier_bdc_projet' pour le bons de commande

            $data['etat_projet'] = array('actif' => 'actif', 'pause' => 'pause', 'prevu' => 'prévu', 'clot' => 'clot');

            $data['etat_selected'] = ($this->input->post('etat_projet') ? $this->input->post('etat_projet') : ($id_projet ? $projet['etat_projet'] : 'actif'));
        }

        $this->view('gestionprojets/modifierprojet', $data);
    }

    public function etatprojet($id_projet = FALSE, $state = FALSE)
    {
        $this->load->model('gestion_projet');

        if ($id_projet && preg_match('/^\d+$/', $id_projet))
            $this->gestion_projet->change_state_projet($id_projet, $state);
    }

    private function do_upload()
    {
        $config['upload_path'] = './bdc/';
        $config['max_size'] = '0';
        $config['overwrite'] = TRUE;
        $config['allowed_types'] = '*';

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('bdc_file'))
            return array('error' => $this->upload->display_errors(), 'data' => NULL);
        else
            return array('error' => NULL, 'data' => $this->upload->data());
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */