<?php

class Enregistrer extends CI_Controller {
    public function questionBasique($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('corps', 'Text', 'required');
            $this->form_validation->set_rules('aide', 'Text');
            $this->form_validation->set_rules('points', 'Number', 'required');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT question.id AS questionId, question.numeroOrdre AS numeroOrdre, tq.nom as type FROM question LEFT JOIN type_question AS tq ON question.type_id = tq.id WHERE question.id=?";
                $question = $this->db->query($sql, $id)->result();

                if(isset($question [0])){
                    $sql = "UPDATE question SET corps = ?, aide = ?, points = ? WHERE id = ?";

                    $this->db->query($sql, array(
                        $_POST["corps"],
                        $_POST["aide"],
                        $_POST["points"],
                        $id
                    ));
                    
                    date_default_timezone_set('Europe/Paris');
                    echo $question[0]->type . " n°" . $question[0]->numeroOrdre . " modifié à " .  date("H:i:s");
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function texteATrou($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('corps', 'Text', 'required');
            $this->form_validation->set_rules('aide', 'Text');
            $this->form_validation->set_rules('points', 'Number', 'required');
            $this->form_validation->set_rules('texteATrou', 'Text');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT question.id AS questionId, question.numeroOrdre AS numeroOrdre, tq.nom as type FROM question LEFT JOIN type_question AS tq ON question.type_id = tq.id WHERE question.id=?";
                $question = $this->db->query($sql, $id)->result();

                if(isset($question [0])){
                    $sql = "UPDATE question SET corps = ?, aide = ?, points = ? WHERE id = ?";

                    $this->db->query($sql, array(
                        $_POST["corps"],
                        $_POST["aide"],
                        $_POST["points"],
                        $id
                    ));
                    
                    $sql = "SELECT count(id) FROM sub_question WHERE id = ?";
                    $nbSousQuestion = $this->db->query($sql, $id)->result();

                    if($nbSousQuestion != "0"){
                        $sql = "DELETE answer FROM answer LEFT JOIN sub_question ON answer.sous_question_id = sub_question.id WHERE sub_question.question_id = ?";
                        $this->db->query($sql, $id);

                        $sql = "DELETE good_answer FROM good_answer LEFT JOIN sub_question ON good_answer.sous_question_id = sub_question.id WHERE sub_question.question_id = ?";
                        $this->db->query($sql, $id);

                        $sql = "DELETE sub_question FROM sub_question WHERE question_id = ?";
                        $this->db->query($sql, $id);
                    }

                    $confirmation = $this->trou->transformer($_POST["texteATrou"], $id);
                    
                    date_default_timezone_set('Europe/Paris');
                    echo $question[0]->type . " n°" . $question[0]->numeroOrdre . " modifié à " .  date("H:i:s");
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function propositionQuestionATrou($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('corps', 'Text', 'required');
            $this->form_validation->set_rules('commentaire', 'Text');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT id FROM good_answer WHERE id=?";
                $bonneReponse = $this->db->query($sql, $id)->result();

                if(isset($bonneReponse[0])){
                    $sql = "UPDATE good_answer SET corps = ?, commentaire = ? WHERE id = ?";
                    
                    $this->db->query($sql, array(
                        $_POST["corps"],
                        $_POST["commentaire"],
                        $id
                    ));
                    
                    date_default_timezone_set('Europe/Paris');
                    echo "Proposition d'identifiant " . $bonneReponse[0]->id . " modifiée à " .  date("H:i:s");
                }
                
                else{
                    echo "Reponse non trouvée";
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function propositionQuizz($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('corps', 'Text', 'required');
            $this->form_validation->set_rules('commentaire', 'Text');
            $this->form_validation->set_rules('vraiFaux', 'boolean', 'callback_boolean_check');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT id FROM good_answer WHERE id=?";
                $bonneReponse = $this->db->query($sql, $id)->result();

                if(isset($bonneReponse[0])){
                    $sql = "UPDATE good_answer SET corps = ?, commentaire = ?, verite = ? WHERE id = ?";

                    if($_POST["vraiFaux"] === "true"){
                        $vraiFaux = 1;
                    }

                    else{
                        $vraiFaux = 0;
                    }
                    
                    $this->db->query($sql, array(
                        $_POST["corps"],
                        $_POST["commentaire"],
                        $vraiFaux,
                        $id
                    ));
                    
                    date_default_timezone_set('Europe/Paris');
                    echo "Proposition d'identifiant " . $bonneReponse[0]->id . " modifiée à " .  date("H:i:s");
                }
                
                else{
                    echo "Reponse non trouvée";
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function propositionAssociation($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('premiereProposition', 'Text', 'required');
            $this->form_validation->set_rules('secondeProposition', 'Text', 'required');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT id FROM sub_question WHERE id=?";
                $premiereProposition = $this->db->query($sql, $id)->result();

                if(isset($premiereProposition[0])){
                    $sql = "UPDATE sub_question SET corps = ? WHERE id = ?";
                    
                    $this->db->query($sql, array(
                        $_POST["premiereProposition"],
                        $id
                    ));
                    
                    $sql = "UPDATE good_answer SET corps = ? WHERE sous_question_id = ?";
                    
                    $this->db->query($sql, array(
                        $_POST["secondeProposition"],
                        $id
                    ));
                    
                    date_default_timezone_set('Europe/Paris');
                    echo "Proposition d'identifiant " . $premiereProposition[0]->id . " modifiée à " .  date("H:i:s");
                }
                
                else{
                    echo "Reponse non trouvée";
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function propositionVraiOuFaux($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('premiereProposition', 'Text', 'required');
            $this->form_validation->set_rules('radioVraiFaux', 'boolean', 'callback_boolean_check');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT id FROM sub_question WHERE id=?";
                $premiereProposition = $this->db->query($sql, $id)->result();

                if(isset($premiereProposition[0])){
                    $sql = "UPDATE sub_question SET corps = ? WHERE id = ?";
                    
                    $this->db->query($sql, array(
                        $_POST["premiereProposition"],
                        $id
                    ));
                    
                    $sql = "UPDATE good_answer SET verite = ? WHERE sous_question_id = ?";
                        
                    $this->db->query($sql, array(
                        $_POST["radioVraiFaux"],
                        $id
                    ));
                    
                    date_default_timezone_set('Europe/Paris');
                    echo "Proposition d'identifiant " . $premiereProposition[0]->id . " modifiée à " .  date("H:i:s");
                }
                
                else{
                    echo "Reponse non trouvée";
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function creerNouvelleQuestion($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('typeQuestion[]', 'Options', 'required');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT id FROM exercise WHERE id=?";
                $exercice = $this->db->query($sql, $id)->result();

                if(isset($exercice[0])){
                    $sql = "SELECT id, nom FROM type_question WHERE nom=?";
                    $typeQuestion = $this->db->query($sql, $_POST["typeQuestion"])->result();
                    
                    $sql = "SELECT MAX(numeroOrdre) AS rangMaximum FROM question WHERE exercice_id=?";
                    $numeroOrdre = $this->db->query($sql, $exercice[0]->id)->result()[0]->rangMaximum + 1;
                    
                    $sql = "INSERT INTO question (corps, exercice_id, numeroOrdre, type_id, points) VALUES (?, ?, ?, ?, ?)";
                    $this->db->query($sql, array(
                        "Futur énoncé",
                        $exercice[0]->id,
                        $numeroOrdre,
                        $typeQuestion[0]->id,
                        1
                    ));

                }
                
                else{
                    echo "Exercice non trouvé";
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function creerNouvelleProposition($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));

            $this->load->database();
            
            $sql = "SELECT type_question.nom AS type FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE question.id=? ";
            $question = $this->db->query($sql, $id)->result();

            if(isset($question[0])){
                switch($question[0]->type){
                    case "Question à trou":
                        $sql = "INSERT INTO good_answer (corps, question_id) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            "",
                            $id
                        ));
                        
                        break;
                        
                    case "Quizz":
                        $sql = "INSERT INTO good_answer (corps, question_id) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            "",
                            $id
                        ));
                        
                        break;
                        
                    case "Nouvelles associations":
                        $sql = "INSERT INTO sub_question (corps, question_id) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            "",
                            $id
                        ));
                        
                        $sql = "INSERT INTO good_answer (corps, sous_question_id) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            "",
                            $this->db->insert_id()
                        ));
                        echo $this->db->insert_id();
                        
                        break;
                        
                    case "Nouveau vrai ou faux":
                        $sql = "INSERT INTO sub_question (corps, question_id) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            "",
                            $id
                        ));
                        
                        $sql = "INSERT INTO good_answer (verite, sous_question_id) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            "1",
                            $this->db->insert_id()
                        ));
                        
                        break;
                }  
            }
                
            else{
                echo "Question non trouvée";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function intro($id)
    {
        if($this->authentification->permission("A")){
            $this->load->helper(array('form', 'url'));
            
            $this->form_validation->set_rules('contenu', 'Text', 'required');

            if ($this->form_validation->run() === True)
            {
                $this->load->database();
            
                $sql = "SELECT *, note.id AS billetId FROM note LEFT JOIN exercise ON note.id = exercise.intro_id WHERE exercise.id=?";
                $billet = $this->db->query($sql, $id)->result();

                if(isset($billet[0])){
                    $sql = "UPDATE note SET contenu = ? WHERE id = ?";
                    
                    $this->db->query($sql, array(
                        $_POST["contenu"],
                        $billet[0]->billetId
                    ));
                    
                    date_default_timezone_set('Europe/Paris');
                    echo "Intro modifiée à " .  date("H:i:s");
                }
                
                else{
                    echo "Intro non trouvée";
                }
            }
            
            else{
                echo "Formulaire non valide";
            }
        }
        
        else{
            show_404();
        }
    }
    
    public function boolean_check($booleen)
    {
        if($booleen === "true" || $booleen === "false" || $booleen === "1" || $booleen === "0")
        {
            return true;
        }
        
        else
        {
            $this->form_validation->set_message('boolean_check', 'La valeur envoyé doit être un booléen.');
            return false;
        }
    }
}