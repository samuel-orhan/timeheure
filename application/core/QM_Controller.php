<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class QM_Controller extends CI_Controller
{

    protected $groups = NULL;
    protected $daysOfWeek = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');
    protected $monthOfYear = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');

    public function __construct()
    {
        parent::__construct();

        $this->load->library('ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->helper('url');

        if (!$this->ion_auth->logged_in() &&
            $this->uri->segment(1) !== 'login')
            redirect('/login', 'refresh');

        if ($this->groups != NULL && $this->ion_auth->logged_in()) {
            if (!$this->ion_auth->in_group($this->groups) &&
                $this->uri->segment(1) !== 'index')
                redirect('/index', 'refresh');
        }
    }

    protected function view($page, $data = NULL)
    {
        $_OPTIONS = $this->option();
        $_HEADER = $this->header();
        $_NAV = array('menu' => $this->navigation());

        $view_data = array(
            'HEAD' => $this->load->view('layout/head', NULL, TRUE),
            'OPTIONS' => $this->load->view('layout/options', $_OPTIONS, TRUE),
            'HEADER' => $this->load->view('layout/header', $_HEADER, TRUE),
            'NAV' => $this->load->view('layout/nav', $_NAV, TRUE),
            'CONTENT' => $this->load->view($page, $data, TRUE)
        );

        $this->load->view('layout/index', $view_data);
    }

    private function navigation()
    {
        $nav = array();

        $nav[] = array(
            'title' => 'Accueil',
            'icon' => 'i_house',
            'content' => '/index'
        );

        // SUPER ADMINISTRATEUR

        if ($this->ion_auth->is_admin()) {
            $nav[] = array(
                'title' => 'Gérer les utilisateurs',
                'icon' => 'i_users_2',
                'content' => '/superadmin/showusers'
            );
        } else {
            $nav[] = array(
                'title' => 'Mes données personnelles',
                'icon' => 'i_user',
                'content' => '/user/update_user'
            );
        }

        // ADMINISTRATEUR

        if ($this->ion_auth->in_group('admin')) {
            // Liste du sous-menu

            $content = array();
            $content[] = array(
                'title' => 'Agenda des projets',
                'content' => '/admin/feuillepresence'
            );

            $content[] = array(
                'title' => 'Horaires annuels',
                'content' => '/admin/heuresup'
            );

            // Liste des utilisateurs

            $query = $this->db->get('users');
            foreach ($query->result() as $row) {
                $groups = $this->ion_auth->get_users_groups($row->id)->result();

                foreach ($groups as $group)
                    if ($group->name == 'user')
                        $content[] = array(
                            'title' => 'Semainier de ' . $row->username,
                            'content' => '/admin/semainier/' . $row->id
                        );
            }

            $query->free_result();

            // Mise au point de la navigation

            $nav[] = array(
                'title' => 'Gestion du personnel',
                'icon' => 'i_calendar',
                'content' => $content
            );
        }

        // SUPER UTILISATEUR

        if ($this->ion_auth->in_group('superuser')) {
            $nav[] = array(
                'title' => 'Gestion clients',
                'icon' => 'i_v-card',
                'content' => '/superuser/listeclient'
            );
        }

        if ($this->ion_auth->in_group('user') || $this->ion_auth->in_group('superuser')) {
            $nav[] = array(
                'title' => 'Gestion des projets',
                'icon' => 'i_folder',
                'content' => array(
                    array(
                        'title' => 'Prévus',
                        'content' => '/gestionprojets/projet/prevu'
                    ),
                    array(
                        'title' => 'Actifs',
                        'content' => '/gestionprojets/projet/actif'
                    ),
                    array(
                        'title' => 'En pause',
                        'content' => '/gestionprojets/projet/pause'
                    ),
                    array(
                        'title' => 'Cloturés',
                        'content' => '/gestionprojets/projet/clot'
                    ),
                ),
            );
        }

        // UTILISATEUR

        if ($this->ion_auth->in_group('user')) {
            $nav[] = array(
                'title' => 'Vos feuilles de temps',
                'icon' => 'i_timer',
                'content' => array(
                    array(
                        'title' => 'Semainier',
                        'content' => '/user/semainier'
                    ),
                    array(
                        'title' => 'Agenda',
                        'content' => '/user/feuillepresence'
                    ),
                    array(
                        'title' => 'Timer',
                        'content' => '/user/modifiertimer'
                    ),
                    array(
                        'title' => 'Heures sup',
                        'content' => '/user/heuresup'
                    )
                )
            );
        }

        // Pout tous le monde

        $nav[] = array(
            'title' => 'Déconnection',
            'icon' => 'i_cross',
            'content' => '/logout'
        );

        return $nav;
    }

    private function header()
    {
        $user = $this->ion_auth->user()->row();
        $data = array();

        // Résumé de ma journée (timers)

        $query = $this->db->where(array('demarrage <=' => date('Y-m-d 23:59:59'), 'arret >=' => date('Y-m-d 00:00:00'), 'id' => $user->id))->order_by('demarrage', 'asc')->get('timers');
        $data['timers'] = $query->num_rows();

        $data['times'] = array();
        foreach ($query->result() as $row)
            $data['times'][] = array(
                'demarrage' => $row->demarrage,
                'arret' => $row->arret
            );

        $query->free_result();

        // Résumé de ma semaine

        $query = $this->db->where(array('user' => $user->id, 'date >=' => date('Y:m:d', strtotime("last Monday")), 'date <=' => strtotime('+1 Week', strtotime("next Monday"))))->get('presence');

        $data['days'] = array();
        foreach ($query->result() as $row) {
            $sec = 0;

            if ($row->matin_fin != NULL && $row->matin_debut != NULL)
                $sec += strtotime($row->date . ' ' . $row->matin_fin) - strtotime($row->date . ' ' . $row->matin_debut);
            if ($row->am_fin != NULL && $row->am_debut != NULL)
                $sec += strtotime($row->date . ' ' . $row->am_fin) - strtotime($row->date . ' ' . $row->am_debut);

            $data['days'][] = array(
                'jour' => $this->daysOfWeek[(int)date('w', strtotime($row->date . ' 00:00:00'))],
                'presence' => (floor($sec / 3600) . 'H ' . (floor($sec / 60) % 60) . 'mn.')
            );
        }
        /*
          if ($this->ion_auth->in_group('superuser') || $this->ion_auth->in_group('admin') || $this->ion_auth->in_group('superadmin')) {
          // Compte les projet sans participations

          $data['closed'] = array();

          $query = $this->db->query("SELECT `projets`.`id_projet`, `projets`.`nom_projet`, `clients`.`nom_client` FROM `projets` LEFT JOIN `clients` ON `clients`.`id_client`=`projets`.`id_client` WHERE NOT EXISTS (SELECT `id` FROM `participants` WHERE `participants`.`id_projet` = `projets`.`id_projet`) AND `projets`.`etat_projet` LIKE 'actif'");
          foreach ($query->result_array() as $projet) {
          $data['closed'][] = $projet;
          }
          }
         */
        return $data;
    }

    private function option()
    {
        $user = $this->ion_auth->user()->row();

        $data = array();
        $data['user'] = $user;
        $data['group'] = '';

        $groups = $this->ion_auth->get_users_groups($user->id)->result();
        foreach ($groups as $group)
            $data['group'] .= ($data['group'] !== '' ? ', ' : '') . $group->description;

        return $data;
    }

}

/* End of file QM_Controller.php */
/* Location: ./application/core/QM_Controller.php */