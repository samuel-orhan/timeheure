<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Gestion_timer extends CI_Model {
    public $username;

    public function __construct() {
        parent::__construct();

        $this->load->library('ion_auth');
        $this->load->library('session');

        $this->username = $this->ion_auth->user()->row()->username;
    }

    public function temps_par_projet($id_projet = FALSE, $username = FALSE) {
        // Préparation de la clause WHERE

        $where = array();
        $where['id_projet'] = $id_projet;

        if($username != FALSE)
            $where['username'] = $username;

        // Exécution de la requête et calcul

        $temps_projet = 0;

        $query = $this->db->select('timers.*, users.username')->where($where)->join('users', 'timers.id = users.id')->get('timers');
        foreach ($query->result() as $duree)
            $temps_projet += (strtotime($duree->arret) - strtotime($duree->demarrage));

        $query->free_result();

        // FIN

        return $temps_projet;
    }
}