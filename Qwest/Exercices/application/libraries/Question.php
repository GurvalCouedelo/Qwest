<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Question{
    public function initialisation()
    {
        $CI = &get_instance();
        // Netoyage de la base de données
        
        $sql = 'DELETE ag FROM answer_good_answer ag LEFT JOIN answer a ON ag.answer_id = a.id WHERE a.utilisateur_id = ?';
        $CI->db->query($sql, $CI->session->id);
        
        $sql = 'DELETE FROM answer WHERE utilisateur_id = ?';
        $CI->db->query($sql, $CI->session->id);
        
//        Initialisation des variables
                    
        $sql = 'SELECT count(id) FROM question WHERE exercice_id = ?';
        $iterationMax = intval($CI->db->query($sql, $CI->session->exercice)->row_array()["count(id)"]);
        
        if($iterationMax === 0){
            show_404();
        }
        
        $nombrePages = $this->compter_nombres_pages();
        
        $sql = 'SELECT titre, id FROM exercise WHERE id = ?';
        $titre = $CI->db->query($sql, $CI->session->exercice)->row_array()["titre"];
        $id = $CI->db->query($sql, $CI->session->exercice)->row_array()["id"];
        
        $donneesExercice = array("exercice" => $id, "exerciceTitre" => $titre, "question" => 1, "iterationMax" => $iterationMax, "groupePasses" => array(), "nombrePages" => $nombrePages, "page" => 1);
        $CI->session->set_userdata("donneesExercice", $donneesExercice);
    }
    
    
    public function questionSuivante()
    {
        $CI = &get_instance();
        
        $sql = "SELECT id FROM question WHERE exercice_id = ? AND numeroOrdre = ?";
        $question = $CI->db->query($sql, array($CI->session->exercice, $CI->session->donneesExercice["question"] + 1))->row_array()["id"];
        
        $donneesExerciceTemp = $CI->session->donneesExercice;
        $donneesExerciceTemp["question"] += 1; 
        var_dump($donneesExerciceTemp["question"]);
        
        if($question === null){
            $CI->session->set_userdata("donneesExercice", $donneesExerciceTemp);
            return;
        }
        
        else{
            $sql = "SELECT g.* FROM association_group g LEFT JOIN question q on q.groupe_id = g.id WHERE g.exercice_id = ? AND numeroOrdre = ?";
            $groupe = $CI->db->query($sql, array($CI->session->exercice, $donneesExerciceTemp["question"]))->row_array();
            
            if($groupe !== null && in_array($groupe["id"], $donneesExerciceTemp["groupePasses"]))
            {
                $sql = "SELECT count(id) FROM question WHERE groupe_id = ?";
                $nbQuestions = $CI->db->query($sql, array($groupe["id"]))->row_array()["count(id)"] - 1;
                $donneesExerciceTemp["question"] += $nbQuestions; 
                
            }
            
            else{
                array_push($donneesExerciceTemp["groupePasses"], $groupe["id"]);
            }
        }
        
        $donneesExerciceTemp["page"] += 1;

        $CI->session->set_userdata("donneesExercice", $donneesExerciceTemp);
    }
    
    
    public function ajouterGroupe($groupe)
    {
        $CI = &get_instance();
        
        $donneesExerciceTemp = $CI->session->donneesExercice;
        $donneesExerciceTemp["groupePasses"];
        if(!in_array($groupe, $donneesExerciceTemp["groupePasses"])){
            array_push($donneesExerciceTemp["groupePasses"], $groupe);
        }
        
        $CI->session->set_userdata("donneesExercice", $donneesExerciceTemp);
    }
    
    
    public function compter_nombres_pages()
    {
        $CI = &get_instance();
        
        $sql = 'SELECT id, groupe_id FROM question WHERE exercice_id = ?';
        $questions = $CI->db->query($sql, $CI->session->exercice)->result();
        
        $nombreQuestion = 0;
        $tableauGroupe = array();
        
        foreach($questions as $questionBoucle){
            if($questionBoucle->groupe_id === null)
            {
                $nombreQuestion++;
            }
            
            elseif($questionBoucle->groupe_id !== null && !in_array($questionBoucle->groupe_id, $tableauGroupe)){
                $nombreQuestion++;
                array_push($tableauGroupe, $questionBoucle->groupe_id);
            }
        }
        
        return $nombreQuestion;
    }
}