<?php
class Exercice extends CI_Controller {
    public function index()
    {
        if($this->authentification->permission("C")){
            if(!isset($this->session->donneesExercice["exercice"]) || $this->session->donneesExercice["exercice"] !== $this->session->exercice){
                $this->question->initialisation();
            }
            
            $sql = 'SELECT question.*, type_question.nom FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE exercice_id = ? ORDER BY numeroOrdre';
            $questions = $this->db->query($sql, $this->session->exercice)->result();

            $i = 1;

            while($i !== $this->session->donneesExercice["question"]) {
                
                if($this->session->donneesExercice["question"] > $this->session->donneesExercice["iterationMax"])
                {     
                    $id = $this->session->exercice;

                    redirect($this->session->URL . "/resultats", 'location', 301);

                }

                $i++;
            }

    //        Décrémentation pour supporter le décalage des cases avec le tableau
            $i -= 1;

            $this->session->set_userdata("question", $questions[$i]);

            if($questions[$i]->nom === "Question à trou"){
                $this->questionATrou();
            }

            elseif($questions[$i]->nom === "Quizz"){
                $this->quizz();
            }

            elseif($questions[$i]->nom === "Association"){
                $this->association();
            }

            elseif($questions[$i]->nom === "Nouvelles associations"){
                $this->nouvellesAssociations();
            }

            elseif($questions[$i]->nom === "Vrai ou faux"){
                $this->vraiOuFaux();
            }

            elseif($questions[$i]->nom === "Nouveau vrai ou faux"){

                $this->nouveauVraiOuFaux();
            }

            elseif($questions[$i]->nom === "Texte à trou"){
                $this->texteATrou();
            }

            elseif($questions[$i]->nom === "Nouveau texte à trou"){
                $this->nouveauTexteATrou();
            }

            elseif($questions[$i]->nom === "Ouverte"){
                $this->ouverte();
            }

            else{
                echo "Cette page ne supporte que les questions à trou et les quizz";
            }
        }
        
    }
    
    public function questionATrou(){
        if(isset($this->session->question) && $this->session->question->nom === "Question à trou"){
            
            $this->load->view('templates/header');
            $this->load->view('templates/exerciceNonGroupeHaut');
            $this->load->view('exercice/questionATrou');
            $this->load->view('templates/exerciceNonGroupeBas');
            $this->load->view('templates/footer');
        }
        else{show_404();}
    }
    
    public function questionATrouReponse(){
        if(isset($this->session->question) && $this->session->question->nom === "Question à trou"){
            $this->load->helper(array('form', 'url'));
        
            $this->form_validation->set_rules('corps', 'Text', 'required');

            if ($this->form_validation->run() === True)
            {
                $sql = "INSERT INTO answer (question_id, utilisateur_id, corps, dateCreation, verite, reponse_assoc_id, groupe_id) VALUES (?, ?, ?, NOW(), null, null, null)";
                $this->db->query($sql, array(
                    $this->session->question->id,
                    $this->session->id,
                    htmlentities($_POST["corps"])
                ));
                
                $this->question->questionSuivante();
            }
        }
        
        redirect($this->session->URL . "/exercice/", 'location', 301);
    }
    
    public function quizz(){
        if(isset($this->session->question) && $this->session->question->nom === "Quizz"){
            $sql = 'SELECT id, corps FROM good_answer WHERE question_id = ?';
            $propositions = $this->db->query($sql, $this->session->question->id)->result();
            
            $this->load->view('templates/header');
            $this->load->view('templates/exerciceNonGroupeHaut');
            $this->load->view('exercice/quizz', array("propositions" => $propositions));
            $this->load->view('templates/exerciceNonGroupeBas');
            $this->load->view('templates/footer');
        }
    }
    
    public function quizzReponse(){
        if(isset($this->session->question) && $this->session->question->nom === "Quizz"){
            $this->load->helper(array('form', 'url'));
            $this->form_validation->set_rules('reponsesQuizz[]', 'Options', 'required');

            if ($this->form_validation->run() === True)
            {
                foreach($_POST["reponsesQuizz"] as $rqBoucle){
                    $sql = "INSERT INTO answer (question_id, utilisateur_id, corps, dateCreation, verite, reponse_assoc_id, groupe_id) VALUES (?, ?, null, NOW(), null, ?, null)";
                    $this->db->query($sql, array(
                        $this->session->question->id,
                        $this->session->id,
                        $rqBoucle
                    ));
                }
                
                $this->question->questionSuivante();
            }
        }
        
        redirect($this->session->URL . "/exercice/", 'location', 301);
    }
    
    
    public function nouvellesAssociations(){
       if(isset($this->session->question) && $this->session->question->nom === "Nouvelles associations"){
            $sql = 'SELECT sub_question.corps as proposition, sub_question.id as sousQuestionId  FROM sub_question LEFT JOIN question ON sub_question.question_id = question.id WHERE question.id = ?';
            $questions = $this->db->query($sql, $this->session->question->id)->result();
            $this->session->set_userdata("questions", $questions);
           
            $sql = 'SELECT good_answer.id as reponseId, good_answer.corps FROM good_answer LEFT JOIN sub_question ON good_answer.sous_question_id = sub_question.id LEFT JOIN question ON sub_question.question_id = question.id WHERE question.id = ? ORDER BY RAND()';
            $propositions = $this->db->query($sql, $this->session->question->id)->result();
          
            $this->load->view('templates/header');
            $this->load->view('templates/exerciceNonGroupeHaut');
            $this->load->view('exercice/nouvellesAssociations', array(
                "propositions" => $propositions,
                "questions" => $questions
            ));
            $this->load->view('templates/exerciceNonGroupeBas');
            $this->load->view('templates/footer');
        }
    }
    
    public function nouvellesAssociationsReponse(){
        if(isset($this->session->question) && $this->session->question->nom === "Nouvelles associations"){
            $this->load->helper(array('form', 'url'));
        
            $this->form_validation->set_rules('associations[]', 'Options', 'required');
            
            if ($this->form_validation->run() === True)
            {
                $i = 0;
                
                foreach($_POST["associations"] as $reponseBoucle){
                    $scission = preg_split("#/#", $reponseBoucle);
                    
                    if(count($scission) === 2){
                        $sql = "INSERT INTO answer (sous_question_id, utilisateur_id, dateCreation, reponse_assoc_id) VALUES (?, ?, NOW(), ?)";
                        $this->db->query($sql, array(
                            $scission[0],
                            $this->session->id,
                            $scission[1]
                        ));

                        $i++;
                    }
                }
                
                $this->question->questionSuivante();
            }

        }
        
        redirect($this->session->URL . "/exercice/", 'location', 301);
    }
    
    
    public function nouveauVraiOuFaux(){
       if(isset($this->session->question) && $this->session->question->nom === "Nouveau vrai ou faux"){
            $sql = 'SELECT sub_question.corps, sub_question.id FROM sub_question LEFT JOIN good_answer ON sub_question.id = good_answer.sous_question_id WHERE sub_question.question_id = ?';
            $propositions = $this->db->query($sql, $this->session->question->id)->result();
            $this->session->set_userdata("propositions", $propositions);
          
          
            $this->load->view('templates/header');
            $this->load->view('templates/exerciceNonGroupeHaut');
            $this->load->view('exercice/nouveauVraiOuFaux', array("propositions" => $propositions));
            $this->load->view('templates/exerciceNonGroupeBas');
            $this->load->view('templates/footer');
        }
    }
    
    public function nouveauVraiOuFauxReponse(){
        if(isset($this->session->question) && $this->session->question->nom === "Nouveau vrai ou faux"){
            $this->load->helper(array('form', 'url'));
            $this->form_validation->set_rules('vraiOuFaux[]', 'Options', 'required');

            if ($this->form_validation->run() === True)
            {
                $i = 0;
                
                foreach($_POST["vraiOuFaux"] as $reponseBoucle){ 
                    $sql = "INSERT INTO answer (sous_question_id, utilisateur_id, dateCreation, verite) VALUES (?, ?, NOW(), ?)";
                    $this->db->query($sql, array(
                        $this->session->propositions[$i]->id,
                        $this->session->id,
                        $reponseBoucle
                    ));
                    
                    $i++;
                }
                
                $this->question->questionSuivante();
            }

        }
        
        redirect($this->session->URL . "/exercice/", 'location', 301);
    }
    
    
    public function nouveauTexteATrou(){
        if(isset($this->session->question) && $this->session->question->nom === "Nouveau texte à trou"){
            $sql = 'SELECT sub_question.corps, sub_question.trouOuListe, ga.id AS idPropo, ga.corps AS corpsPropo, ga.sous_question_id AS idSousQuestion FROM sub_question LEFT JOIN good_answer ga ON sub_question.id = ga.sous_question_id LEFT JOIN question ON sub_question.question_id = question.id WHERE question.id = ? ORDER BY sub_question.id, RAND()';
            $propositions = $this->db->query($sql, $this->session->question->id)->result();
            
            $sql = 'SELECT * FROM sub_question WHERE question_id = ?';
            $questions = $this->db->query($sql, $this->session->question->id)->result();
            $this->session->set_userdata("questions", $questions);
            
//            var_dump($propositions);die;
          
            $this->load->view('templates/header');
            $this->load->view('templates/exerciceNonGroupeHaut');
            $this->load->view('exercice/nouveauTexteATrou', array("propositions" => $propositions));
            $this->load->view('templates/exerciceNonGroupeBas');
            $this->load->view('templates/footer');
        }
    }
    
    public function nouveauTexteATrouReponse(){
        if(isset($this->session->question) && $this->session->question->nom === "Nouveau texte à trou"){
            $this->load->helper(array('form', 'url'));
        
            $this->form_validation->set_rules('texteATrou[]', 'Options', 'required');
            
            if ($this->form_validation->run() === True)
            {
                $i = 0;
                
                foreach($_POST["texteATrou"] as $reponseBoucle){
                    if($this->session->questions[$i]->trouOuListe === "T"){
                        $sql = "INSERT INTO answer (sous_question_id, utilisateur_id, dateCreation, corps) VALUES (?, ?, NOW(), ?)";
                        $this->db->query($sql, array(
                            $this->session->questions[$i]->id,
                            $this->session->id,
                            htmlentities($reponseBoucle)
                        ));
                    }
                    
                    else if($this->session->questions[$i]->trouOuListe === "L"){
                        $sql = "INSERT INTO answer (sous_question_id, utilisateur_id, dateCreation, reponse_assoc_id) VALUES (?, ?, NOW(), ?)";
                        $this->db->query($sql, array(
                            $this->session->questions[$i]->id,
                            $this->session->id,
                            $reponseBoucle
                        ));
                    }
                    
                    
                    $i++;
                }
                
                $this->question->questionSuivante();
            }

        }
        
        redirect($this->session->URL . "/exercice/", 'location', 301);
    }
    
    public function ouverte(){
        if(isset($this->session->question) && $this->session->question->nom === "Ouverte"){
            $this->load->helper(array('form', 'url'));
        
            $this->form_validation->set_rules('ouverte', 'text', 'required');

            $this->load->view('templates/header');
            $this->load->view('templates/exerciceNonGroupeHaut');
            $this->load->view('exercice/ouverte');
            $this->load->view('templates/exerciceNonGroupeBas');
            $this->load->view('templates/footer');

        }
    }
    
    public function ouverteReponse(){
        if(isset($this->session->question) && $this->session->question->nom === "Ouverte"){
            $this->load->helper(array('form', 'url'));
            
            if($this->session->permission === "A"){
                $this->question->questionSuivante();
            }
            
            else{
                
                $this->form_validation->set_rules('corps', 'text', 'required');
                
                if ($this->form_validation->run() === True)
                {
                    $sql = "INSERT INTO train_discussion () VALUES ()";
                    $this->db->query($sql);
                    $idTrain = $this->db->insert_id();
                    
                    $sql = "SELECT id FROM user WHERE permission = 'A'";
                    $adminId = $this->db->query($sql)->row_array()["id"];
                    
                    $sql = "INSERT INTO message (fil_id, envoyeur_id, dateCreation, contenu, lu, type, destinataire_id, question_id) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
                    $this->db->query($sql, array(
                        $idTrain,
                        $this->session->id,
                        htmlentities($_POST["corps"]),
                        false,
                        "R",
                        $adminId,
                        $this->session->question->id
                    ));
                                    
                    $this->question->questionSuivante();
                }
            }
        }
        
        redirect($this->session->URL . "/exercice/", 'location', 301);
    }
}