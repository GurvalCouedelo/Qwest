<?php

class Supprimer extends CI_Controller {
    public function question($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));

            $this->load->database();
            
            $sql = "SELECT question.id AS questionId, question.numeroOrdre AS numeroOrdre, tq.nom as type FROM question LEFT JOIN type_question AS tq ON question.type_id = tq.id WHERE question.id=?";
            $question = $this->db->query($sql, $id)->result();

            if(isset($question [0])){
                $sql = "DELETE FROM answer WHERE question_id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE answer FROM answer LEFT JOIN sub_question ON answer.sous_question_id = sub_question.id LEFT JOIN question ON sub_question.question_id = question.id WHERE question.id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE answer FROM answer  WHERE reponse_assoc_id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE FROM good_answer WHERE question_id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE good_answer FROM good_answer LEFT JOIN sub_question ON good_answer.sous_question_id = sub_question.id LEFT JOIN question ON sub_question.question_id = question.id WHERE question.id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE FROM sub_question WHERE question_id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE FROM question WHERE id = ?";
                
                $this->db->query($sql, array($id));
                echo $id;
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function reponse($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));

            $this->load->database();
            
            $sql = "SELECT * FROM good_answer WHERE id=?";
            $reponse = $this->db->query($sql, $id)->result();

            if(isset($reponse[0])){
                
                $sql = "DELETE answer FROM answer  WHERE reponse_assoc_id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE FROM good_answer WHERE id = ?";
                $this->db->query($sql, array($id));
                
                
                echo $id;
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function sousQuestion($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));

            $this->load->database();
            
            $sql = "SELECT * FROM sub_question WHERE id=?";
            $sousQuestion = $this->db->query($sql, $id)->result();

            if(isset($sousQuestion[0])){
                $sql = "DELETE answer FROM answer LEFT JOIN sub_question ON answer.sous_question_id = sub_question.id WHERE sub_question.id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE answer FROM answer  LEFT JOIN good_answer ON reponse_assoc_id = good_answer.id WHERE good_answer.sous_question_id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE good_answer FROM good_answer LEFT JOIN sub_question ON good_answer.sous_question_id = sub_question.id WHERE sub_question.id = ?";
                $this->db->query($sql, array($id));
                
                $sql = "DELETE FROM sub_question WHERE id = ?";
                $this->db->query($sql, array($id));
                
                echo $id;
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function ressource($questionId, $ressourceId)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));

            $this->load->database();
            
            $sql = "DELETE FROM means_question WHERE question_id = ? AND means_id = ?";
            $this->db->query($sql, array(
                $questionId,
                $ressourceId
            ));
                
        }
        
        else{
            show_404();
        }
    }
}