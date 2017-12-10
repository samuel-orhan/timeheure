<?php

if (!defined('BASEPATH'))
  exit('No direct script access allowed');

class User extends QM_Controller {

  protected $authorisation = array('user');

  public function __construct() {
    parent::__construct();
  }

  public function semainier() {
    $data = array();

    $data['title'] = 'Récapulatif des 4 dernières semaines de ' . $user = $this->ion_auth->user()->row()->username;
    $data['message'] = $this->session->flashdata('message');

    $user = $this->ion_auth->user()->row();

    // Semainier

    $data['semainier'] = array();

    // De dimanche suivant
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

    $this->view('/user/resumepresence', $data);
  }

  public function feuillepresence() {
    $data = array();

    $data['title'] = 'Agenda de ' . $user = $this->ion_auth->user()->row()->username;
    $data['message'] = $this->session->flashdata('message');


    $this->view('/user/feuillepresence', $data);
  }

  public function modifiertimer() {
    $data = array();

    $data['title'] = 'Gérer vos timers';
    $data['message'] = $this->session->flashdata('message');

    $user = $this->ion_auth->user()->row();

    // Modification de la date

    $data['date'] = date('Y-m-d');
    if ($this->input->post('date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->input->post('date')))
      $data['date'] = $this->input->post('date');

    $dayName = $this->daysOfWeek[(int) date('w', strtotime($data['date']))];
    $monthName = $this->monthOfYear[(int) date('n', strtotime($data['date'])) - 1];

    $data['datetitle'] = $dayName . ' ' . date('d', strtotime($data['date'])) . ' ' . $monthName . ' ' . date('Y', strtotime($data['date']));

    // Un formulaire

    $this->form_validation->set_rules('id_timer', 'timer', 'required|integer');
    $this->form_validation->set_rules('id_projet', 'projet', 'required|integer');

    $this->form_validation->set_rules('date_demarrage', 'date', 'required');
    $this->form_validation->set_rules('date_arret', 'date', 'required');
    $this->form_validation->set_rules('demarrage', 'heure démarrage', 'required');
    $this->form_validation->set_rules('arret', 'heure d\'arrêt', 'required');

    $this->form_validation->set_rules('notes', 'notes', '');

    if ($this->form_validation->run() == true) {
      // Pré validation

      $depart = $this->input->post('date_demarrage') . ' ' . $this->input->post('demarrage');
      $fin = $this->input->post('date_arret') . ' ' . $this->input->post('arret');

      $request = 'SELECT * FROM `timers` WHERE ';
      $request .= '("' . $depart . '" > `demarrage` AND "' . $depart . '" < `arret` AND ';
      $request .= '"' . $fin . '" > `demarrage` AND "' . $fin . '" < `arret`) AND `id_timer` != ' . $this->input->post('id_timer');
      $request .= ' AND `id` = ' . $user->id;

      $query = $this->db->query($request);

      if ($query->num_rows() > 0)
        $data['message'] = "Mise à jour impossible, vos timers se croisent";
      else {
        $update = array(
            'demarrage' => date('Y-m-d H:i:s', strtotime($this->input->post('date_demarrage') . ' ' . $this->input->post('demarrage'))),
            'arret' => date('Y-m-d H:i:s', strtotime($this->input->post('date_arret') . ' ' . $this->input->post('arret'))),
            'notes_temps' => $this->input->post('notes')
        );

        $this->db->where(array('id' => $user->id, 'id_timer' => $this->input->post('id_timer')))->update('timers', $update);

        if ($this->db->affected_rows() > 0)
          $data['message'] = "Timer modifié";
        else
          $data['message'] = "Impossible de modifier ce timer";
      }
    }

    // Liste journée

    $query = $this->db->select('timers.*, projets.nom_projet, clients.nom_client')
            ->where(array('timers.demarrage <=' => $data['date'] . ' 23:59:5900:00:00', 'timers.arret >=' => $data['date'] . ' 00:00:00', 'timers.id' => $user->id))
            ->join('projets', 'timers.id_projet = projets.id_projet')
            ->join('clients', 'projets.id_client = clients.id_client')
            ->order_by('demarrage', 'asc')
            ->get('timers');

    $data['timers'] = array();
    foreach ($query->result() as $row)
      $data['timers'][] = array(
          'id_timer' => $row->id_timer,
          'id_projet' => $row->id_projet,
          'demarrage' => $row->demarrage,
          'arret' => $row->arret,
          'notes' => $row->notes_temps,
          'identite' => $row->nom_client . ', ' . $row->nom_projet
      );

    $this->view('/user/modifiertimer', $data);
  }

  public function modifierpresence($date = FALSE) {
    if (!$date)
      redirect('/user/feuillepresence', 'refresh');

    $data = array();
    $data['title'] = "Temps de présence";

    $time = strtotime($date . ' 00:00:00');

    $year = date('Y', $time);
    $day = date('j', $time);

    $dayName = $this->daysOfWeek[(int) date('w', $time)];
    $monthName = $this->monthOfYear[(int) date('n', $time) - 1];

    $data['dateTexte'] = 'Du ' . strtolower($dayName) . ' ' . $day . ' ' . $monthName . ' ' . $year;

    // Formulaire

    $this->form_validation->set_rules('matin_debut', 'arrivé le matin', 'required');
    $this->form_validation->set_rules('matin_fin', 'départ le matin', 'required');
    $this->form_validation->set_rules('am_debut', 'arrivé l\'après midi', 'required');
    $this->form_validation->set_rules('am_fin', 'départ l\'après midi', 'required');

    $this->form_validation->set_rules('date', 'Jour de présence', 'required');

    $user = $this->ion_auth->user()->row();
    if ($this->form_validation->run() == true) {
      if ($user->id) {
        $data = array(
            'date' => $this->input->post('date'),
            'matin_debut' => $this->input->post('matin_debut'),
            'matin_fin' => $this->input->post('matin_fin'),
            'am_debut' => $this->input->post('am_debut'),
            'am_fin' => $this->input->post('am_fin'),
            'user' => $user->id
        );

        $query = $this->db->where(array('date' => $this->input->post('date'), 'user' => $user->id))->get('presence');
        if ($query->num_rows() > 0)
          $this->db->where('id_presence', $query->row('id_presence'))->update('presence', $data);
        else
          $this->db->insert('presence', $data);

        if ($this->db->affected_rows() > 0) {
          $this->session->set_flashdata('message', "Feuille de présence modifié");
          redirect("/user/feuillepresence", 'refresh');
        } else {
          $this->session->set_flashdata('message', "Impossible de modifier la feuille de présence");
          redirect("/user/feuillepresence", 'refresh');
        }
      }
    } else {
      $presence = FALSE;

      $query = $this->db->where(array('date' => $date, 'user' => $user->id))->get('presence');
      if ($query->num_rows() > 0)
        $presence = $query->row();

      $data['message'] = (validation_errors() ? validation_errors() : $this->session->flashdata('message'));

      $data['date'] = array('name' => 'date',
          'id' => 'date',
          'type' => 'hidden',
          'value' => ($date ? $date : ($presence ? $presence->date : '')),
      );

      $data['matin_debut'] = array('name' => 'matin_debut',
          'id' => 'matin_debut',
          'type' => 'text',
          'class' => 'time',
          'value' => ($this->input->post('matin_debut') ? $this->input->post('matin_debut') : ($presence ? date('H:i', strtotime($date . ' ' . $presence->matin_debut)) : '09:00')),
      );
      $data['matin_fin'] = array('name' => 'matin_fin',
          'id' => 'matin_fin',
          'type' => 'text',
          'class' => 'time',
          'value' => ($this->input->post('matin_fin') ? $this->input->post('matin_fin') : ($presence ? date('H:i', strtotime($date . ' ' . $presence->matin_fin)) : '12:30')),
      );
      $data['am_debut'] = array('name' => 'am_debut',
          'id' => 'am_debut',
          'type' => 'text',
          'class' => 'time',
          'value' => ($this->input->post('am_debut') ? $this->input->post('am_debut') : ($presence ? date('H:i', strtotime($date . ' ' . $presence->am_debut)) : '14:00')),
      );
      $data['am_fin'] = array('name' => 'am_fin',
          'id' => 'am_fin',
          'type' => 'text',
          'class' => 'time',
          'value' => ($this->input->post('am_fin') ? $this->input->post('am_fin') : ($presence ? date('H:i', strtotime($date . ' ' . $presence->am_fin)) : '17:30')),
      );
    }

    $this->view('/user/modifierpresence', $data);
  }

  public function update_user() {
    $data = array();
    $data['title'] = "Modification mes données personnelles";

    $user = $this->ion_auth->user()->row();

    if (!$user) {
      $this->session->set_flashdata('message', "Impossible de vous identifier");
      redirect("/superadmin/feuillepresence", 'refresh');
    }

    /*
     * Création d'un utilisateur
     */

    $this->form_validation->set_rules('username', 'Nom d\'utilisateur', 'required');

    $this->form_validation->set_rules('first_name', 'Nom', 'required');
    $this->form_validation->set_rules('last_name', 'Prénom', 'required');
    $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email');
    $this->form_validation->set_rules('password', 'Mot de passe', 'min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');

    if ($this->form_validation->run() == true) {
      $update = array();

      $update['username'] = $this->input->post('username');
      $update['email'] = $this->input->post('email');

      if ($this->input->post('password') != FALSE)
        $update['password'] = $this->input->post('password');

      $update['first_name'] = $this->input->post('first_name');
      $update['last_name'] = $this->input->post('last_name');
      ;

      if ($this->form_validation->run() == true && $this->ion_auth->update($user->id, $update)) {
        //check to see if we are creating the user
        //redirect them back to the admin page

        $this->session->set_flashdata('message', "Utilisateur modifié");
        redirect("/index", 'refresh');
      }
    } else {
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

      $this->view('user/updateuser', $data);
    }
  }

  public function presence() {
    $data = array();
    $user = $this->ion_auth->user()->row();

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
            'url' => '/user/modifierpresence/' . $index,
            'title' => $day_presence[$index]['temps'] . ' de présence',
            'allDay' => true,
            'start' => $index,
            'color' => '#A2E8A2',
            'textColor' => '#000000'
        );
      else
        $data[] = array(
            'url' => '/user/modifierpresence/' . $index,
            'title' => 'ajouter un jour de présence',
            'allDay' => true,
            'start' => $index
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

    // Durée sur chaque projet jour par jour

    foreach ($projets as $p) {
      foreach ($p['days'] as $day => $jj) {
        $h = floor($jj / 3600);
        $m = floor($jj / 60) - $h * 60;

        $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn.';

        $data[] = array(
            'url' => '/user/modifierpresence/' . $day,
            'title' => $hm . ' sur ' . $p['nom_projet'],
            'allDay' => true,
            'start' => date('c', strtotime($day . ' 00:00:00')),
            'color' => '#E8E8A2',
            'textColor' => '#000000'
        );
      }
    }

    // Durée total sur les projet jour par jour

    foreach ($total_timer as $day => $pjj) {
      $h = floor($pjj / 3600);
      $m = floor($pjj / 60) - $h * 60;

      $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn.';

      $data[] = array(
          'url' => '/user/modifierpresence/' . $day,
          'title' => $hm . ' sur vos projets.',
          'allDay' => true,
          'start' => date('c', strtotime($day . ' 00:00:00')),
          'color' => '#F0A8A8',
          'textColor' => '#000000'
      );
    }

    // Fin, affichage des résultats

    $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
  }

  public function heuresup($year = FALSE) {
    $data = array();

    $data['title'] = 'Rapport annuel sur mes horaires, année ' . ($year ? $year : date('Y'));
    $data['message'] = $this->session->flashdata('message');

    // Depuis le début de l'année

    $debut_annee = strtotime(date('Y-01-01'));
    if ($year !== FALSE)
      $debut_annee = strtotime(date($year . '-01-01'));

    $query = $this->db->get('users');
    foreach ($query->result() as $row)
      $users[$row->id] = $row;
    $query->free_result();

    $data['user'] = array();
    $user = $this->ion_auth->user()->row();

    // Utilisateur par utilisateur
    // Depuis le début de l'année à aujourd'hui

    $data['user'] = array();

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
            $mustdo += 60 * 60 * 3.5;
          }
          if ($row->am_fin != NULL && $row->am_debut != NULL) {
            $secondes += strtotime($row->date . ' ' . $row->am_fin) - strtotime($row->date . ' ' . $row->am_debut);
            $mustdo += 60 * 60 * 3.5;
          }

          if (($row->matin_fin != NULL && $row->matin_debut != NULL) ||
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

      $data['user'][$i] = array(
          'month' => date('F', $month),
          'temps' => $hm,
          'diff' => $dhm,
          'workdays' => $workdays,
      );
    }

    $this->view('/user/heuresup', $data);
  }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */