<?php

if (!defined('BASEPATH'))
  exit('No direct script access allowed');

class Gestion_projet extends CI_Model {

  public $username;

  public function __construct() {
    parent::__construct();

    $this->load->library('ion_auth');
    $this->load->library('session');

    $this->username = $this->ion_auth->user()->row()->username;
  }

  public function get_all_projet($limitstart = 0, $limitlength = 10, $sorton = FALSE, $sortdir = FALSE, $search = FALSE, $projettype = FALSE) {
    ////////////////////////////////////////////////////////////////////////
    // Total

    if ($this->ion_auth->in_group('user') && !(
      $this->ion_auth->in_group('superuser') ||
      $this->ion_auth->in_group('admin') ||
      $this->ion_auth->in_group('superadmin'))) {

      $user = $this->ion_auth->user()->row();

      $this->db->join('participants', 'projets.id_projet = participants.id_projet');
      $this->db->where('participants.id', $user->id);
    }

    if ($search != FALSE && trim($search) !== '') {
      $this->db->where(
        '(projets.date_creation REGEXP(\'.*(' . $search . ')+.*\') OR ' .
        'projets.bdc_projet REGEXP(\'.*(' . $search . ')+.*\') OR ' .
        'clients.nom_client REGEXP(\'.*(' . $search . ')+.*\') OR ' .
        'projets.nom_projet REGEXP(\'.*(' . $search . ')+.*\'))'
      );
      $this->db->join('clients', 'projets.id_client = clients.id_client');
    }
    if ($projettype != FALSE)
      $this->db->where('etat_projet', $projettype);


    $this->db->select('projets.id_projet');
    $total = $this->db->get('projets')->num_rows();

    ////////////////////////////////////////////////////////////////////////
    // Requete

    if ($this->ion_auth->in_group('user') && !(
      $this->ion_auth->in_group('superuser') ||
      $this->ion_auth->in_group('admin') ||
      $this->ion_auth->in_group('superadmin'))) {

      $user = $this->ion_auth->user()->row();

      $this->db->join('participants', 'projets.id_projet = participants.id_projet');
      $this->db->where('participants.id', $user->id);
    }

    $this->db->select('clients.index_client, clients.nom_client, clients.id_client, users.username, projets.*');

    $this->db->join('clients', 'projets.id_client = clients.id_client');
    $this->db->join('users', 'projets.id = users.id');

    $col_sortable = array('projets.date_creation', 'projets.bdc_projet', 'clients.nom_client', 'projets.nom_projet');

    if ($sorton == FALSE)
      $this->db->order_by('clients.index_client', 'asc');
    elseif ($sortdir == FALSE)
      $this->db->order_by($col_sortable[$sorton - 1], 'asc');
    else
      $this->db->order_by($col_sortable[$sorton - 1], $sortdir);

    if ($search != FALSE && trim($search) !== '') {
      $this->db->where(
        '(projets.date_creation REGEXP(\'.*(' . $search . ')+.*\') OR ' .
        'projets.bdc_projet REGEXP(\'.*(' . $search . ')+.*\') OR ' .
        'clients.nom_client REGEXP(\'.*(' . $search . ')+.*\') OR ' .
        'projets.nom_projet REGEXP(\'.*(' . $search . ')+.*\'))'
      );
    }
    if ($projettype != FALSE)
      $this->db->where('etat_projet', $projettype);

    $this->db->limit($limitlength, $limitstart);
    $query = $this->db->get('projets');

    // Valeur de retour

    $data = array(
      'totalresults' => $total,
      'results' => array()
    );

    foreach ($query->result_array() as $row)
      $data['results'][] = $row;

    $query->free_result();

    // FIN

    return $data;
  }

  public function get_my_projet() {
    $user_id = $this->ion_auth->user()->row()->id;

    $this->db->join('participants', 'projets.id_projet = participants.id_projet');
    $this->db->where(array('participants.id' => $user_id, 'etat_projet LIKE' => 'actif'));

    $this->db->select('clients.index_client, clients.nom_client, clients.id_client, users.username, projets.*');
    $this->db->distinct();
    $this->db->join('clients', 'projets.id_client = clients.id_client');
    $this->db->join('users', 'projets.id = users.id');
    $this->db->order_by('clients.index_client', 'asc');

    $query = $this->db->get('projets');

    // Valeur de retour

    $data = array();
    foreach ($query->result() as $row) {
      $projet = array();

      foreach ($row as $k => $v)
        $projet[$k] = $v;

      $data[] = $projet;
    }
    $query->free_result();

    // FIN

    return $data;
  }

  public function get_my_dues() {
    $user_id = $this->ion_auth->user()->row()->id;

    $this->db->join('participants', 'projets.id_projet = participants.id_projet');
    $this->db->where('participants.id', $user_id);

    $this->db->select(', clients.nom_client, clients.id_client, users.username, projets.*');
    $this->db->distinct();
    $this->db->join('clients', 'projets.id_client = clients.id_client');
    $this->db->join('users', 'projets.id = users.id');
    $this->db->order_by('projets.dead_line', 'desc');

    $query = $this->db->get('projets');

    // Valeur de retour

    $data = array();
    foreach ($query->result() as $row) {
      $projet = array();

      foreach ($row as $k => $v)
        $projet[$k] = $v;

      $data[] = $projet;
    }
    $query->free_result();

    // FIN

    return $data;
  }

  public function get_one_projet($id_projet = FALSE) {
    $data = array();

    if ($id_projet && preg_match('/^\d+$/', $id_projet)) {
      $where = array();
      $where['projets.id_projet'] = $id_projet;

      if ($this->ion_auth->in_group('user') && !(
        $this->ion_auth->in_group('superuser') ||
        $this->ion_auth->in_group('admin') ||
        $this->ion_auth->in_group('superadmin'))) {

        $user = $this->ion_auth->user()->row();

        $this->db->join('participants', 'projets.id_projet = participants.id_projet');
        $where['participants.id'] = $user->id;
      }

      $this->db->where($where);
      $this->db->select('clients.nom_client, users.username, projets.*');
      $this->db->join('clients', 'projets.id_client = clients.id_client');
      $this->db->join('users', 'projets.id = users.id');
      $this->db->order_by('clients.index_client', 'asc');

      $query = $this->db->get('projets');

      $data = $query->row_array();
    }

    // FIN

    return $data;
  }

  public function set_projet($id_projet = FALSE, $data = FALSE, $participants = FALSE) {
    if ($id_projet && preg_match('/^\d+$/', $id_projet))
      $this->db->where('id_projet', $id_projet)->update('projets', $data);
    else
      $this->db->insert('projets', $data);

    $id = ($id_projet && preg_match('/^\d+$/', $id_projet) ? $id_projet : $this->db->insert_id());

    $this->db->where('id_projet', $id)->delete('participants');

    if (isset($participants) && $participants)
      foreach ($participants as $user)
        $this->db->insert('participants', array('id' => $user, 'id_projet' => $id));

    return $this->db->affected_rows();
  }

  public function unset_participation($id_projet, $id_user) {
    $this->db->where(array('id_projet' => $id_projet, 'id' => $id_user))->delete('participants');
  }

  public function change_state_projet($id_projet = FALSE, $etat = FALSE) {
    $this->db->where(array('id_projet' => $id_projet))->update('projets', array('etat_projet' => $etat));
  }

}