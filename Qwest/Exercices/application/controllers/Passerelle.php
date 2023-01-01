<?php
class Passerelle extends CI_Controller {
    public function index($token, $lien = "")
    {
        $this->load->library('session');
        $this->load->database();
        $this->load->helper('url');
        
        $sql = 'SELECT utilisateur_id, exercice_id FROM passerelle WHERE token=?';
        $passerelle = $this->db->query($sql, $token)->row_array();
        
        if(isset($passerelle["utilisateur_id"])){
            $ID = $passerelle["utilisateur_id"];
            $exercice = $passerelle["exercice_id"];
            
            $sql = 'SELECT pseudo, permission FROM user WHERE ID=?';
            $utilisateur = $this->db->query($sql, $ID)->row_array();
            $permission = $utilisateur["permission"];
            $pseudo = $utilisateur["pseudo"];
            
            
            $this->session->set_userdata("question", null);
            $this->session->set_userdata("donneesExercice", null);
            $this->session->set_userdata("enregistre", false);
            
            $this->session->set_userdata("id", $ID);
            $this->session->set_userdata("permission", $permission);
            $this->session->set_userdata("exercice", $exercice);
            $this->session->set_userdata("pseudo", $pseudo);
            
            $sql = 'DELETE FROM passerelle WHERE token=?';
            $this->db->query($sql, $token);
            
            $this->session->set_userdata("LHS", "https://titann.fr/");
            $this->session->set_userdata("LHSS", "https://titann.fr/");
            $this->session->URL = "https://exercices.titann.fr/index.php";
            $this->session->URL_AUTRE = "https://exercices.titann.fr/";
            
//            $this->session->set_userdata("LHS", "http://127.0.0.1/Qwest/web/app_dev.php/");
//            $this->session->set_userdata("LHSS", "http://127.0.0.1/Qwest/web/");
//            $this->session->URL = "http://127.0.0.1/Qwest/Exercices/index.php";
//            $this->session->URL_AUTRE = "http://127.0.0.1/Qwest/Exercices/";
            
            $lien = preg_replace('#%7E#', '/', $lien);
            $lien = preg_replace('#~#', '/', $lien);
            redirect($lien, 'refresh');
        }
        
        else{
            echo "Jeton introuvable";
        }
    }
}