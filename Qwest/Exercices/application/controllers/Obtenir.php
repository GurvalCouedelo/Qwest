<?php

class Obtenir extends CI_Controller {
    public function questions($id)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT id FROM exercise WHERE id=?";
            $exercice = $this->db->query($sql, $id)->result();
            
            if(isset($exercice[0])){
                $sql = "SELECT question.id AS id, question.corps AS corps, type_question.nom as nom_type FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE exercice_id=? ORDER BY question.numeroOrdre";
                $questions = $this->db->query($sql, $id)->result();
                
                header('Content-Type: application/json');
                echo json_encode(
                    array(
                        "listeQuestions" => $questions
                ));
            }
            
            else{
                show_404();
            }
            
        }
    }
    
    public function changerDisponibilite($id)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT id, publie FROM exercise WHERE id=?";
            $exercice = $this->db->query($sql, $id)->result()[0];
            
            if(isset($exercice)){
                if($exercice->publie == "0"){
                    $publie = "1";
                }
                
                else{
                    $publie = "0";
                }
                
                $sql = "UPDATE exercise SET publie = ? WHERE id = ?";
                $this->db->query($sql, array(
                    $publie,
                    $id
                ));
                
                if($exercice->publie == "0"){
                    echo "<span class=\"masquer\">Masquer l'exercice</span>";
                }
                else{
                    echo "<span class=\"publier\">Publier l'exercice</span>";
                }
            }
            
            else{
                show_404();
            }
            
        }
    }
    
}