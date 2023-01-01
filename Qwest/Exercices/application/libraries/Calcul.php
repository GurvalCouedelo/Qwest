<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calcul{
    public $sommePoints = array();
    
    public $tableauEnregistrement = array();
    
    public function resultats()
    {
        $CI = &get_instance();
        
//        Total des points
        
        $sql = 'SELECT sum(question.points) AS total FROM question WHERE question.exercice_id = ?';
        $totalPoints = $CI->db->query($sql, $CI->session->exercice)->result()[0]->total;
        
        
        $sql = 'SELECT question.*, type_question.nom as type FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE question.exercice_id = ?';
        $questions = $CI->db->query($sql, $CI->session->exercice)->result();
//        var_dump($questions);
//        die;
        
        foreach($questions as $questionBoucle){
            if($questionBoucle->type === "Question à trou"){
                $this->questionATrou($questionBoucle);
            }
            
            if($questionBoucle->type === "Quizz"){
                $this->quizz($questionBoucle);
            }
            
            if($questionBoucle->type === "Nouvelles associations"){
                $this->association($questionBoucle);
            }
            
            if($questionBoucle->type === "Nouveau vrai ou faux"){
                $this->vraiOuFaux($questionBoucle);
            }
            
            if($questionBoucle->type === "Nouveau texte à trou"){
                $this->texteATrou($questionBoucle);
            }
        }
        
        
//        Enregistrement des corrections en base de donnees
        
        foreach($this->tableauEnregistrement as $enregistrementBoucle){
            $sql = 'UPDATE answer SET correction = ? WHERE id = ?';
            $CI->db->query($sql, array(
                $enregistrementBoucle[1],
                $enregistrementBoucle[0]
            ));
        }
        
            
        return array("resultat" => array_sum($this->sommePoints), "totalPoints" => $totalPoints);
    }
    
    public function questionATrou($question){
        $CI = &get_instance();
        
        $sql = 'SELECT good_answer.* FROM good_answer LEFT JOIN question ON good_answer.question_id = question.id WHERE question.id = ?';
        $bonnesReponses = $CI->db->query($sql, array(
            $question->id
        ))->result();
        
        $sql = 'SELECT answer.* FROM answer WHERE question_id = ? AND answer.utilisateur_id = ?';
        $reponse = $CI->db->query($sql, array(
            $question->id,
            $CI->session->id
        ))->result()[0];
        
        $reussite = false;
        foreach($bonnesReponses as $bonneReponseBoucle){
            if(preg_match("#$bonneReponseBoucle->corps#i", html_entity_decode($reponse->corps)))
            {
                $this->sommePoints[$question->numeroOrdre] = $question->points;
                array_push($this->tableauEnregistrement, array($reponse->id, true));
                $reussite = true;
            }
        }
        
        if($reussite === false){
            array_push($this->tableauEnregistrement, array($reponse->id, false));
        }
    }
    
    public function quizz($question){
        $CI = &get_instance();
        
        $sql = 'SELECT answer.id AS reponseId, good_answer.* FROM answer LEFT JOIN good_answer ON answer.reponse_assoc_id = good_answer.id WHERE good_answer.question_id = ? AND answer.utilisateur_id = ?';
        $bonnesReponses = $CI->db->query($sql, array(
            $question->id,
            $CI->session->id
        ))->result();
        
        $fausseReponse = false;
        
        foreach($bonnesReponses as $reponseBoucle){
            if($reponseBoucle->verite === "1"){
                if(!isset($pointsParBonneReponse[$question->numeroOrdre]) && $fausseReponse === false){
                    $sql = "SELECT count(good_answer.id) AS coefficient FROM good_answer WHERE question_id = ? AND verite = ?";
                    $pointsParBonneReponse[$question->numeroOrdre] = 1 / intval($CI->db->query($sql, 
                        array(
                            $question->id,
                            1
                        ))->result()[0]->coefficient) * $question->points;
                }
                
                array_push($this->tableauEnregistrement, array($reponseBoucle->reponseId, true));
                
                if($fausseReponse === false){
                    if(!isset($this->sommePoints[$question->numeroOrdre])){
                        $this->sommePoints[$question->numeroOrdre] = $pointsParBonneReponse[$question->numeroOrdre];
                    }

                    else{
                        $this->sommePoints[$question->numeroOrdre] += $pointsParBonneReponse[$question->numeroOrdre];
                    }
                }
            }

            else{
                $this->sommePoints[$question->numeroOrdre] = 0;
                array_push($this->tableauEnregistrement, array($reponseBoucle->reponseId, false));
                $fausseReponse = true;
            }
        }
        
    }
    
    public function association($question){
        $CI = &get_instance();
        
        $sql = 'SELECT sub_question.id AS sousQuestionId, good_answer.sous_question_id AS associe, answer.id AS reponseId FROM answer 
        LEFT JOIN good_answer ON answer.reponse_assoc_id = good_answer.id LEFT JOIN sub_question on answer.sous_question_id = sub_question.id WHERE sub_question.question_id = ? AND answer.utilisateur_id = ?';
        $reponses = $CI->db->query($sql, array(
            $question->id,
            $CI->session->id
        ))->result();
        

        foreach($reponses as $reponseBoucle){
            if($reponseBoucle->sousQuestionId === $reponseBoucle->associe){
                if(!isset($pointsParBonneReponse[$question->numeroOrdre])){
                    $sql = "SELECT count(id) AS coefficient FROM sub_question WHERE question_id = ?";
                    $pointsParBonneReponse[$question->numeroOrdre] = 1 / intval($CI->db->query($sql, $question->id)->result()[0]->coefficient) * $question->points;
                }
                
                array_push($this->tableauEnregistrement, array($reponseBoucle->reponseId, true));
                
                if(!isset($this->sommePoints[$question->numeroOrdre])){
                    $this->sommePoints[$question->numeroOrdre] = $pointsParBonneReponse[$question->numeroOrdre];
                }

                else{
                    $this->sommePoints[$question->numeroOrdre] += $pointsParBonneReponse[$question->numeroOrdre];
                }
            }
            
            else{
                    
                array_push($this->tableauEnregistrement, array($reponseBoucle->reponseId, false));
            }
        }
    }
    
    public function vraiOuFaux($question){
        $CI = &get_instance();
        
        $sql = 'SELECT answer.verite, good_answer.verite AS bonneReponse, answer.id  FROM answer LEFT JOIN sub_question on answer.sous_question_id = sub_question.id LEFT JOIN good_answer ON sub_question.id = good_answer.sous_question_id WHERE sub_question.question_id = ? AND answer.utilisateur_id = ?';
        $reponses = $CI->db->query($sql, array(
            $question->id,
            $CI->session->id
        ))->result();
        
        
        foreach($reponses as $reponseBoucle){
            if($reponseBoucle->verite === $reponseBoucle->bonneReponse){
                if(!isset($pointsParBonneReponse[$question->numeroOrdre])){
                    $sql = "SELECT count(id) AS coefficient FROM sub_question WHERE question_id = ?";
                    $pointsParBonneReponse[$question->numeroOrdre] = 1 / intval($CI->db->query($sql, $question->id)->result()[0]->coefficient) * $question->points;
                }
                
                array_push($this->tableauEnregistrement, array($reponseBoucle->id, true));
                
                if(!isset($this->sommePoints[$question->numeroOrdre])){
                    $this->sommePoints[$question->numeroOrdre] = $pointsParBonneReponse[$question->numeroOrdre];
                }

                else{
                    $this->sommePoints[$question->numeroOrdre] += $pointsParBonneReponse[$question->numeroOrdre];
                }
            }
            
            else{
                array_push($this->tableauEnregistrement, array($reponseBoucle->id, false));
            }
            
        }
//        var_dump($reponses);
//        die;
        
    }
    
    public function texteATrou($question){
        $CI = &get_instance();
        
        $sql = 'SELECT answer.corps as reponse, answer.verite, answer.id, sub_question.trouOuListe, sub_question.id as sousQuestionId, good_answer.verite FROM answer LEFT JOIN sub_question on answer.sous_question_id = sub_question.id LEFT JOIN good_answer ON answer.reponse_assoc_id = good_answer.id WHERE sub_question.question_id = ?  AND answer.utilisateur_id = ?';
        $reponses = $CI->db->query($sql, array(
            $question->id,
            $CI->session->id
        ))->result();
        
        foreach($reponses as $reponseBoucle){
            if($reponseBoucle->trouOuListe === "T"){
                $sql = 'SELECT * FROM good_answer WHERE sous_question_id = ?';
                $bonnesReponses = $CI->db->query($sql, $reponseBoucle->sousQuestionId)->result();
                $i = 0;
                
                foreach($bonnesReponses as $bonneReponseBoucle){
                    if(preg_match("#$bonneReponseBoucle->corps#i", html_entity_decode($reponseBoucle->reponse))){
                        if(!isset($pointsParBonneReponse[$question->numeroOrdre])){
                            $sql = "SELECT count(id) AS coefficient FROM sub_question WHERE question_id = ?";
                            $pointsParBonneReponse[$question->numeroOrdre] = 1 / intval($CI->db->query($sql, $question->id)->result()[0]->coefficient) * $question->points;
                        }

                        array_push($this->tableauEnregistrement, array($reponseBoucle->id, true));

                        if(!isset($this->sommePoints[$question->numeroOrdre])){
                            $this->sommePoints[$question->numeroOrdre] = $pointsParBonneReponse[$question->numeroOrdre];
                        }

                        else{
                            $this->sommePoints[$question->numeroOrdre] += $pointsParBonneReponse[$question->numeroOrdre];
                        }
                    }
                    
                    $i++;
                }

                if($i === 0){
                    array_push($this->tableauEnregistrement, array($reponseBoucle->id, false));
                }
            }
            
            else if($reponseBoucle->trouOuListe === "L"){
                if($reponseBoucle->verite === "1"){
                    if(!isset($pointsParBonneReponse[$question->numeroOrdre])){
                            $sql = "SELECT count(id) AS coefficient FROM sub_question WHERE question_id = ?";
                            $pointsParBonneReponse[$question->numeroOrdre] = 1 / intval($CI->db->query($sql, $question->id)->result()[0]->coefficient) * $question->points;
                        }

                        array_push($this->tableauEnregistrement, array($reponseBoucle->id, true));

                        if(!isset($this->sommePoints[$question->numeroOrdre])){
                            $this->sommePoints[$question->numeroOrdre] = $pointsParBonneReponse[$question->numeroOrdre];
                        }

                        else{
                            $this->sommePoints[$question->numeroOrdre] += $pointsParBonneReponse[$question->numeroOrdre];
                        }
                }
                
                else{
                    array_push($this->tableauEnregistrement, array($reponseBoucle->id, false));
                }
            }
            
            
            
        }
        
        
        
    }
}