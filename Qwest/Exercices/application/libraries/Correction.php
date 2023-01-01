<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Correction{
    public function obtenirReponses($id)
    {
        $CI = &get_instance();
        
        $sql = 'SELECT answer.* FROM answer WHERE answer.utilisateur_id = ? AND question_id = ?';
        $reponse = $CI->db->query($sql, 
            array(
                $CI->session->id,
                $id
            ))->result();
        
        return $reponse;
    }
    
    public function obtenirBonnesReponses($id)
    {
        $CI = &get_instance();
        
        $sql = 'SELECT good_answer.* FROM good_answer WHERE question_id = ?';
        $reponse = $CI->db->query($sql, $id)->result();
        
        return $reponse;
    }
    
    public function obtenirSousQuestions($id)
    {
        $CI = &get_instance();
        
        $sql = 'SELECT sub_question.*, good_answer.corps AS bonneReponse, good_answer.commentaire FROM good_answer LEFT JOIN sub_question ON good_answer.sous_question_id = sub_question.id  WHERE sub_question.question_id = ? ORDER BY sub_question.id';
        $sousQuestions= $CI->db->query($sql, $id)->result();
        
        return $sousQuestions;
    }
    
    public function obtenirReponsesAssociation($id)
    {
        $CI = &get_instance();
        
        $sql = 'SELECT answer.*, good_answer.corps AS proposition FROM answer LEFT JOIN good_answer ON answer.reponse_assoc_id = good_answer.id LEFT JOIN sub_question ON good_answer.sous_question_id = sub_question.id WHERE sub_question.question_id = ? AND answer.utilisateur_id = ? ORDER BY sub_question.id';
        $reponses = $CI->db->query($sql, array(
            $id,
            $CI->session->id
        ))->result();
        
        return $reponses;
    }
    
    public function obtenirReponsesVraiOuFaux($id)
    {
        $CI = &get_instance();
        
        $sql = 'SELECT answer.correction, good_answer.commentaire, answer.verite AS reponse, good_answer.verite AS bonneReponse, answer.id, sub_question.corps FROM answer LEFT JOIN sub_question ON answer.sous_question_id = sub_question.id LEFT JOIN good_answer ON sub_question.id = good_answer.sous_question_id WHERE sub_question.question_id = ? AND answer.utilisateur_id = ? ORDER BY sub_question.id';
        $reponses = $CI->db->query($sql, array(
            $id,
            $CI->session->id
        ))->result();
        
        return $reponses;
    }
    
    public function obtenirReponsesTexteATrou($id)
    {
        $CI = &get_instance();
        
        $sql = 'SELECT answer.correction, answer.corps as reponse, good_answer.commentaire, good_answer.corps as corpsProposition, sub_question.id as sousQuestionId , sub_question.corps, sub_question.trouOuListe as TOL FROM answer LEFT JOIN sub_question ON answer.sous_question_id = sub_question.id LEFT JOIN good_answer ON answer.reponse_assoc_id = good_answer.id WHERE sub_question.question_id = ? AND answer.utilisateur_id = ? ORDER BY sub_question.id';
        $reponses = $CI->db->query($sql, array(
            $id,
            $CI->session->id
        ))->result();
        
        return $reponses;
    }
    
    public function obtenirBonnesReponsesTexteATrou($sousQuestionId, $TOL)
    {
        $CI = &get_instance();
        
        if($TOL === "T"){
            $sql = 'SELECT * FROM good_answer WHERE sous_question_id = ?';
            $reponses = $CI->db->query($sql, $sousQuestionId)->result();
        }
        
        else if($TOL === "L"){
            $sql = 'SELECT * FROM good_answer WHERE sous_question_id = ? AND verite = 1';
            $reponses = $CI->db->query($sql,$sousQuestionId)->result();
        }
        
        
        return $reponses;
    }
}