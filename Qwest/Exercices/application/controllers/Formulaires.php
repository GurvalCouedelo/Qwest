<?php

class Formulaires extends CI_Controller {
    public function toutEnUn($id)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT question.id AS idQ, type_question.nom AS nom, texteATrou, points, aide, corps FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE question.id=?";
            $question = $this->db->query($sql, $id)->result();
                
            if(isset($question[0])){
                
                
                if($question[0]->nom !== "Nouveau texte à trou"){
                    $sql = "SELECT means.id as mId, extension, alt, lien, matiere_id FROM means LEFT JOIN means_question ON means.id = means_question.means_id LEFT JOIN question ON means_question.question_id = question.id WHERE question.id = ?";
                    $ressources = $this->db->query($sql, $id)->result();
                    $this->question($id, $ressources);
                    $i = 0;
                    
                    
                    if($question[0]->nom === "Question à trou"){
                        $sql = "SELECT * FROM good_answer WHERE question_id=?";
                        $bonnesReponses = $this->db->query($sql, $question[0]->idQ)->result();

                        foreach($bonnesReponses as $bonnesReponsesBoucle){
                            $i++;
                            $this->propositionQuestionATrou($bonnesReponsesBoucle->id, $i);
                        }
                        
                        $this->bouton($question[0]);
                    }

                    else if($question[0]->nom === "Quizz"){
                        $sql = "SELECT * FROM good_answer WHERE question_id=?";
                        $bonnesReponses = $this->db->query($sql, $question[0]->idQ)->result();

                        foreach($bonnesReponses as $bonnesReponsesBoucle){
                            $i++;
                            $this->propositionQuizz($bonnesReponsesBoucle->id, $i);
                        }
                        
                        $this->bouton($question[0]);
                    }

                    else if($question[0]->nom === "Nouvelles associations"){
                        $sql = "SELECT * FROM sub_question WHERE question_id=?";
                        $sousQuestions = $this->db->query($sql, $question[0]->idQ)->result();

                        foreach($sousQuestions as $sousQuestionBoucle){
                            $i++;
                            $this->propositionAssociation($sousQuestionBoucle->id, $i);
                        }
                        
                        $this->bouton($question[0]);
                    }

                    else if($question[0]->nom === "Nouveau vrai ou faux"){
                        $sql = "SELECT * FROM sub_question WHERE question_id=?";
                        $sousQuestions = $this->db->query($sql, $question[0]->idQ)->result();

                        foreach($sousQuestions as $sousQuestionBoucle){
                            $i++;
                            $this->propositionVraiOuFaux($sousQuestionBoucle->id, $i);
                        }
                        
                        $this->bouton($question[0]);
                    }
                }
                
                else{
                    $this->texteATrou($question[0]);
                }
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function question($id, $ressources)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT question.id as questionId, corps, aide, points, tq.nom as type, numeroOrdre FROM question LEFT JOIN type_question AS tq ON question.type_id = tq.id WHERE question.id=?";
            $question = $this->db->query($sql, $id)->result();
            
            if(isset($question[0])){
                
                $this->load->view('formulaires/question', 
                    array(
                        "question" => $question[0],
                        "ressources" => $ressources
                    )
                );
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function propositionQuestionATrou($id, $i = null){
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT id, corps, commentaire FROM good_answer WHERE id=?";
            $bonneReponse = $this->db->query($sql, $id)->result();
            
            if(isset($bonneReponse[0])){
                
                $this->load->view('formulaires/propositionQuestionATrou', 
                    array(
                        "bonneReponse" => $bonneReponse[0],
                        "i" => $i
                    )
                );
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function propositionQuizz($id, $i = null)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT id, corps, commentaire, verite FROM good_answer WHERE id=?";
            $bonneReponse = $this->db->query($sql, $id)->result();
            
            if(isset($bonneReponse[0])){
                
                $this->load->view('formulaires/propositionQuizz', 
                    array(
                        "bonneReponse" => $bonneReponse[0],
                        "i" => $i
                    )
                );
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function propositionAssociation($id, $i = null)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT id, corps FROM sub_question WHERE id=?";
            $premiereProposition = $this->db->query($sql, $id)->result();
            
            if(isset($premiereProposition[0])){
                $sql = "SELECT id, corps FROM good_answer WHERE sous_question_id =?";
                $secondeProposition = $this->db->query($sql, $id)->result();
                
                if(isset($secondeProposition[0])){
                    $this->load->view('formulaires/propositionAssociation', 
                        array(
                            "premiereProposition" => $premiereProposition[0],
                            "secondeProposition" => $secondeProposition[0],
                            "i" => $i
                        )
                    );
                }
                
                else{
                    echo "<p>La seconde proposition est absente.</p>";
                }
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function propositionVraiOuFaux($id, $i = null)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT id, corps FROM sub_question WHERE id=?";
            $premiereProposition = $this->db->query($sql, $id)->result();
            
            if(isset($premiereProposition[0])){
                $sql = "SELECT id, corps, verite FROM good_answer WHERE sous_question_id =?";
                $secondeProposition = $this->db->query($sql, $id)->result();
                
                $this->load->view('formulaires/propositionVraiOuFaux', 
                    array(
                        "premiereProposition" => $premiereProposition[0],
                        "secondeProposition" => $secondeProposition[0],
                        "i" => $i
                    )
                );
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function texteATrou($question)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            if(isset($question)){
                $this->load->view('formulaires/texteATrou', 
                    array(
                        "question" => $question
                    )
                );
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function bouton($question)
    {
        $this->load->view('formulaires/bouton', 
            array("question" => $question)
        );
    }
    
    public function intro($id){
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT * FROM note LEFT JOIN exercise ON note.id = exercise.intro_id WHERE exercise.id=?";
            $billet = $this->db->query($sql, $id)->result();
            
            if(isset($billet[0])){
                $this->load->view('formulaires/intro', 
                    array("billet" => $billet[0])
                );
            }
        }
    }
}