<?php

if (!defined('BASEPATH'))
  exit('No direct script access allowed');

class Admin extends QM_Controller {

  var $authorisation = array('admin');

  public function __construct() {
    parent::__construct();
  }

  public function feuillepresence() {
    $data = array();
    $data['message'] = $this->session->flashdata('message');

    $this->view('/admin/feuillepresence', $data);
  }

  public function presence() {
    $data = array();
    $users = array();


    $query = $this->db->get('users');
    foreach ($query->result() as $row)
      $users[$row->id] = $row;
    $query->free_result();

    foreach ($users as $user) {
      ////////////////////////////////////////////////////////////////////////
      // NEW
      // Calcul des présences de l'utilisateur

      $datedepart = date('Y:m:d', strtotime("previous sunday", (int) $this->input->get_post('start')));
      $datefin = date('Y:m:d', strtotime("next Saturday", (int) $this->input->get_post('end')));

      $query = $this->db->where(array('user' => $user->id, 'date >=' => $datedepart, 'date <=' => $datefin))->get('presence');
      $day_presence = array();

      foreach ($query->result() as $row) {
        $sec = 0;
        if ($row->matin_fin != NULL && $row->matin_debut != NULL)
          $sec += strtotime($row->date . ' ' . $row->matin_fin) - strtotime($row->date . ' ' . $row->matin_debut);
        if ($row->am_fin != NULL && $row->am_debut != NULL)
          $sec += strtotime($row->date . ' ' . $row->am_fin) - strtotime($row->date . ' ' . $row->am_debut);

        $h = floor($sec / 3600);
        $m = floor($sec / 60 - floor($sec / 3600) * 60);
        $str = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';

        if ($sec > 0)
          $day_presence[$row->date] = array(
              'date' => $row->date,
              'temps' => $str,
              'start' => date('c', strtotime($row->date . ' 00:00:00'))
          );
      }

      for ($date = $this->input->get_post('start'); $date <= $this->input->get_post('end'); $date = strtotime('+1 day', $date)) {
        $index = date('Y-m-d', $date);

        if (isset($day_presence[$index]))
          $data[] = array(
              'title' => $user->username . ' ' . $day_presence[$index]['temps'] . ' de présence',
              'allDay' => true,
              'start' => $index,
              'color' => '#A2E8A2',
              'textColor' => '#000000'
          );
      }

      // Données du Timers

      $total_timer = array();
      $projets = array();


      $ticks = $this->db->select('timers.*, users.username, projets.nom_projet')->where(array('timers.id' => $user->id, 'timers.arret >=' => date('Y:m:d H:i:s', $this->input->get_post('start')), 'timers.demarrage <=' => date('Y:m:d H:i:s', $this->input->get_post('end'))))->join('users', 'timers.id = users.id')->join('projets', 'timers.id_projet = projets.id_projet')->get('timers');

      foreach ($ticks->result() as $duree) {
        if (!isset($projets[$duree->id_projet])) {
          $projets[$duree->id_projet] = array();

          $projets[$duree->id_projet]['username'] = $duree->username;
          $projets[$duree->id_projet]['nom_projet'] = $duree->nom_projet;

          $projets[$duree->id_projet]['days'] = array();
        }

        $day = date('Y-m-d', strtotime($duree->demarrage));

        // Temps par projet sur une journée

        if (!isset($projets[$duree->id_projet]['days'][$day]))
          $projets[$duree->id_projet]['days'][$day] = 0;

        $projets[$duree->id_projet]['days'][$day] += (strtotime($duree->arret) - strtotime($duree->demarrage));

        // temps de la journée sur l'ensemble des projets

        if (!isset($total_timer[$day]))
          $total_timer[$day] = 0;

        $total_timer[$day] += (strtotime($duree->arret) - strtotime($duree->demarrage));
      }

      $ticks->free_result();

      // Durée total sur les projet jour par jour

      foreach ($total_timer as $day => $pjj) {
        $h = floor($pjj / 3600);
        $m = floor($pjj / 60) - $h * 60;

        $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn.';

        $data[] = array(
            'url' => '/admin/detail_projet/' . $day . '/' . $user->id,
            'title' => $hm . ' sur les projets de ' . $user->username,
            'allDay' => true,
            'start' => date('c', strtotime($day . ' 00:00:00')),
            'color' => '#D0D0D0',
            'textColor' => '#000000'
        );
      }
    }

    // Fin, affichage des résultats
    // END NEW

    $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
  }

  public function detail_projet($date, $id) {
    $data = array();
    $user = $this->ion_auth->user($id)->row();

    $data['identity'] = $user->username;
    $data['day'] = $date;

    ////////////////////////////////////////////////////////////////////////

    $total_timer = array();
    $projets = array();

    $ticks = $this->db->select('timers.*, users.username, projets.nom_projet')->where(array('timers.id' => $user->id, 'timers.arret >=' => $date . ' 00:00:00', 'timers.demarrage <=' => $date . ' 23:59:59'))->join('users', 'timers.id = users.id')->join('projets', 'timers.id_projet = projets.id_projet')->get('timers');

    foreach ($ticks->result() as $duree) {
      if (!isset($projets[$duree->id_projet])) {
        $projets[$duree->id_projet] = array();

        $projets[$duree->id_projet]['username'] = $duree->username;
        $projets[$duree->id_projet]['nom_projet'] = $duree->nom_projet;

        $projets[$duree->id_projet]['days'] = array();
      }

      $day = date('Y-m-d', strtotime($duree->demarrage));

      // Temps par projet sur une journée

      if (!isset($projets[$duree->id_projet]['days'][$day]))
        $projets[$duree->id_projet]['days'][$day] = 0;

      $projets[$duree->id_projet]['days'][$day] += (strtotime($duree->arret) - strtotime($duree->demarrage));

      // temps de la journée sur l'ensemble des projets

      if (!isset($total_timer[$day]))
        $total_timer[$day] = 0;

      $total_timer[$day] += (strtotime($duree->arret) - strtotime($duree->demarrage));
    }

    $ticks->free_result();

    // Durée sur chaque projet jour par jour

    $data['projet'] = array();

    foreach ($projets as $p) {
      foreach ($p['days'] as $day => $jj) {
        $h = floor($jj / 3600);
        $m = floor($jj / 60) - $h * 60;

        $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn.';

        $data['projet'][] = array(
            'nom' => $p['nom_projet'],
            'duree' => $hm
        );
      }
    }

    // Lancer une vue

    $this->view('/admin/detailprojet', $data);
  }

  public function semainier($id) {
    $data = array();

    $user = $this->ion_auth->user($id)->row();

    $data['title'] = 'Récapulatif des 4 dernières semaines de ' . $user->username;
    $data['message'] = $this->session->flashdata('message');

    // Semainier

    $data['semainier'] = array();

    for ($date = strtotime('last monday'); $date > strtotime('-5 Week'); $date = strtotime('-1 Week', $date)) {
      // Noté

      $query = $this->db->where(array('user' => $user->id, 'date >=' => date('Y:m:d', $date), 'date <' => date('Y:m:d', strtotime('+1 Week', $date))))->get('presence');

      $secsemaine = 0;
      foreach ($query->result() as $row) {
        if ($row->matin_fin != NULL && $row->matin_debut != NULL)
          $secsemaine += strtotime($row->date . ' ' . $row->matin_fin) - strtotime($row->date . ' ' . $row->matin_debut);
        if ($row->am_fin != NULL && $row->am_debut != NULL)
          $secsemaine += strtotime($row->date . ' ' . $row->am_fin) - strtotime($row->date . ' ' . $row->am_debut);
      }

      $h = floor($secsemaine / 3600);
      $m = floor($secsemaine / 60 - floor($secsemaine / 3600) * 60);
      $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';

      $data['semainier'][date('W', $date)] = array();
      $data['semainier'][date('W', $date)]['hmnote'] = $hm;

      // Timer

      $ticks = $this->db->select('timers.id_projet, projets.nom_projet, timers.arret, timers.demarrage')->where(array('timers.id' => $user->id, 'timers.arret >=' => date('Y:m:d H:i:s', $date), 'timers.demarrage <=' => date('Y:m:d H:i:s', strtotime('+1 Week', $date))))->join('users', 'timers.id = users.id')->join('projets', 'timers.id_projet = projets.id_projet')->get('timers');

      $totalprojet = 0;
      $projet = array();

      foreach ($ticks->result() as $row) {
        if (!isset($projet[$row->id_projet]))
          $projet[$row->id_projet] = array('nom' => $row->nom_projet, 'secondes' => 0);

        $duree = (strtotime($row->arret) - strtotime($row->demarrage));
        $projet[$row->id_projet]['secondes'] += $duree;
        $totalprojet += $duree;
      }

      $data['semainier'][date('W', $date)]['projet'] = $projet;
      $data['semainier'][date('W', $date)]['total'] = $totalprojet;
    }

    $this->view('/admin/resumepresence', $data);
  }

  public function cumul_presence($id) {
    $data = array();
    $user = $this->ion_auth->user($id)->row();

    $data['title'] = 'Rapport du temps passé en entreprise pour ' . $user->username;
    $data['message'] = $this->session->flashdata('message');

    $datedebut = strtotime('last month', strtotime('now'));
    $datefin = strtotime('now');

    // Repère des dates de débuts ou des dates de fin

    $this->form_validation->set_rules('datedebut', 'date de début de période', 'require|xss');
    $this->form_validation->set_rules('datefin', 'date de fin de période', 'require|xss');

    if ($this->form_validation->run() == true) {
      if (strtotime($this->input->post('datedebut')) < strtotime($this->input->post('datefin'))) {
        $datedebut = strtotime($this->input->post('datedebut'));
        $datefin = strtotime($this->input->post('datefin'));
      } else {
        $datedebut = strtotime($this->input->post('datefin'));
        $datefin = strtotime($this->input->post('datedebut'));
      }
    }
    $data['datedebut'] = array('name' => 'datedebut',
        'id' => 'datedebut',
        'type' => 'text',
        'class' => 'date',
        'value' => date('Y-m-d', $datedebut)
    );
    $data['datefin'] = array('name' => 'datefin',
        'id' => 'datefin',
        'type' => 'text',
        'class' => 'date',
        'value' => date('Y-m-d', $datefin)
    );

    // ///////////////////////////////////////////////////
    // Liste les temps de présence jour après jours

    $data['semainier'] = array();
    $data['total'] = 0;
    $data['moyenne'] = 0;

    $totalsec = 0;

    $query = $this->db
            ->where(array('user' => $user->id, 'date >=' => date('Y-m-d', $datedebut), 'date <=' => date('Y-m-d', $datefin)))
            ->order_by('date', 'asc')
            ->get('presence');

    // Pour chaque jour entre la date de début et de fin (jour par jour)

    if ($query->num_rows() > 0) {
      foreach ($query->result() as $row) {

        $secday = 0;
        if ($row->matin_fin != NULL && $row->matin_debut != NULL)
          $secday += strtotime($row->date . ' ' . $row->matin_fin) - strtotime($row->date . ' ' . $row->matin_debut);
        if ($row->am_fin != NULL && $row->am_debut != NULL)
          $secday += strtotime($row->date . ' ' . $row->am_fin) - strtotime($row->date . ' ' . $row->am_debut);

        $totalsec += $secday;

        $h = floor($secday / 3600);
        $m = floor($secday / 60 - floor($secday / 3600) * 60);
        $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';

        $data['semainier'][] = array(
            'jour' => $row->date,
            'tempspresence' => $hm,
        );
      }

      $h = floor($totalsec / 3600);
      $m = floor($totalsec / 60 - floor($totalsec / 3600) * 60);
      $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';
      $data['total'] = $hm;

      $moyenne = $totalsec / count($data['semainier']);

      $h = floor($moyenne / 3600);
      $m = floor($moyenne / 60 - floor($moyenne / 3600) * 60);
      $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';
      $data['moyenne'] = $hm;
    }

    $this->view('/admin/cumulpresence', $data);
  }

  public function heuresup($year = FALSE) {
    $data = array();

    $data['title'] = 'Rapport annuel sur les horaires, année '.($year ? $year : date('Y'));
    $data['message'] = $this->session->flashdata('message');

    // Depuis le début de l'année
    
    $debut_annee = strtotime(date('Y-01-01'));
    if($year !== FALSE)
      $debut_annee = strtotime(date($year . '-01-01'));

    $query = $this->db->get('users');
    foreach ($query->result() as $row)
      $users[$row->id] = $row;
    $query->free_result();

    $data['user'] = array();

    foreach ($users as $user) {
      // Utilisateur par utilisateur
      // Depuis le début de l'année à aujourd'hui

      $data['user'][$user->username] = array();

      for ($month = $debut_annee, $i = 0; $month < min(strtotime('now'), $debut_annee + 3600 * 24 * 365); $month = strtotime('+1 month', $month), $i++) {
        // De mois en mois

        $query = $this->db
                ->where(array('user' => $user->id, 'date >=' => date('Y-m-d', $month), 'date <=' => date('Y-m-d', strtotime('+1 month', $month))))
                ->order_by('date', 'asc')
                ->get('presence');

        // Pour chaque jour entre la date de début et de fin (jour par jour)

        $secondes = 0;
        $mustdo = 0;
        $workdays = 0;
        
        if ($query->num_rows() > 0) {
          foreach ($query->result() as $row) {
            if ($row->matin_fin != NULL && $row->matin_debut != NULL) {
              $secondes += strtotime($row->date . ' ' . $row->matin_fin) - strtotime($row->date . ' ' . $row->matin_debut);
              $mustdo += 60*60*3.5;
            }
            if ($row->am_fin != NULL && $row->am_debut != NULL) {
              $secondes += strtotime($row->date . ' ' . $row->am_fin) - strtotime($row->date . ' ' . $row->am_debut);
              $mustdo += 60*60*3.5;
            }
            
            if( ($row->matin_fin != NULL && $row->matin_debut != NULL) ||
               ($row->am_fin != NULL && $row->am_debut != NULL))
              $workdays++;
          }
        }

        // Traduction horaire

        $h = floor($secondes / 3600);
        $m = floor($secondes / 60 - $h * 60);
        $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';

        $diff = ($secondes - $mustdo > 0 ? $secondes - $mustdo : $mustdo - $secondes);
        
        $dh = floor($diff / 3600);
        $dm = floor($diff / 60 - $dh * 60);
        $dhm = ($secondes - $mustdo >= 0 ? '+' : '-') . $dh . 'H ' . ($dm < 10 ? '0' : '') . $dm . 'mn';
        
        $data['user'][$user->username][$i] = array(
            'month' => date('F', $month),
            'temps' => $hm,
            'diff' => $dhm,
            'workdays' => $workdays,
        );
      }
    }
    
    $this->view('/admin/heuresup', $data);
  }
}

/* End of file welcome.php */
    /* Location: ./application/controllers/welcome.php */