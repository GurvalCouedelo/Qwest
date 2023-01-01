<?php
class Exerciseur extends CI_Controller {
    public function index($id, $ancre = "0", $intro = false)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
            
            $sql = "SELECT id, titre, publie FROM exercise WHERE id=?";
            $exercice = $this->db->query($sql, $id)->result();
            
            if(isset($exercice[0])){
                $this->load->view('templates/header');
                $this->load->view('exerciseur/exerciseur.php', 
                    array("id" => $id,
                         "exercice" => $exercice[0])
                );
                
                if($ancre !== "0" || $intro !== false){
                    $this->load->view('exerciseur/ancrer.php', 
                        array(
                            "id" => $id,
                            "ancre" => $ancre,
                            "intro" => $intro
                    ));
                }
                
                $this->load->view('templates/footerBasic');
            }
            
            else{
                show_404();
            }
            
        }
    }
    
    public function creerQuestion($id)
    {
        if($this->authentification->permission("A")){
            $this->load->database();
        
            $sql = "SELECT id FROM exercise WHERE id=?";
            $exercice = $this->db->query($sql, $id)->result();
            
            if(isset($exercice[0])){
                $this->form_validation->set_rules('corps', 'Text', 'required');
                $this->form_validation->set_rules('aide', 'Text');
                $this->form_validation->set_rules('points', 'Number', 'required');
                $this->form_validation->set_rules('type[]', 'Options', 'required');

                if ($this->form_validation->run() === True)
                {

                    $sql = "SELECT id FROM type_question WHERE nom=?";
                    $typeId = $this->db->query($sql, $_POST["type"][0])->result()[0]->id;

                    $sql = "SELECT numeroOrdre FROM question WHERE exercice_id=? ORDER BY numeroOrdre DESC LIMIT 1";
                    $numeroOrdre = $this->db->query($sql, array(
                        $id,

                    ))->result()[0]->numeroOrdre;

                    $sql = "INSERT INTO question (exercice_id, corps, type_id, aide, numeroOrdre, points) VALUES (?, ?, ?, ?, ?, ?)";
                    $this->db->query($sql, array(
                        $id,
                        $_POST["corps"],
                        $typeId,
                        $_POST["aide"],
                        $numeroOrdre + 1,
                        $_POST["points"]
                    ));

                    $questionId = $this->db->insert_id();


                    if($_POST["type"][0] === "Nouvelles associations"){
                        redirect($this->session->URL . "/exerciseur/association/" . $questionId . "/0", 'location', 301);
                    }

                    if($_POST["type"][0] === "Nouveau vrai ou faux"){
                        redirect($this->session->URL . "/exerciseur/vraiOuFaux/" . $questionId . "/0", 'location', 301);
                    }

                    if($_POST["type"][0] === "Nouveau texte à trou"){
                        redirect($this->session->URL . "/exerciseur/texteATrou/" . $questionId, 'location', 301);
                    }

                    redirect($this->session->LHS . "admin/questions-liste/" . $id, 'location', 301);
                }

                $this->load->view('templates/header');
                $this->load->view('exerciseur/creerQuestion', array("id" => $id));
                $this->load->view('templates/footer');
            }
            
            else{
                show_404();
            }
            
        }
    }
    
    public function association($id, $idAssociation)
    {
        if($this->authentification->permission("A")){
            $sql = "SELECT question.id as id, corps, type_question.nom as type FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE question.id=?";
            $question = $this->db->query($sql, $id)->result();
            
            if(isset($question[0])){
                $this->load->database();
                $this->form_validation->set_rules('corps', 'Text', 'required');
                $this->form_validation->set_rules('complement', 'Text', 'required');
                $this->form_validation->set_rules('commentaire', 'Text');

                $donneesFormulaire = array();
                if($idAssociation !== "0"){
                    $sql = "SELECT sub_question.*, good_answer.corps AS complement, good_answer.commentaire FROM sub_question LEFT JOIN good_answer ON sub_question.id = good_answer.sous_question_id WHERE sub_question.id = ?";
                    $donneesFormulaire = $this->db->query($sql, $idAssociation)->result();
                    if(empty($donneesFormulaire)){
                        show_404();
                    }
                }

                if ($this->form_validation->run() === True)
                {
                    if(empty($donneesFormulaire)){
                        $sql = "INSERT INTO sub_question (question_id, corps) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            $id,
                            $_POST["corps"],
                        ));
                        $idInsertion = $this->db->insert_id();

                        $sql = "INSERT INTO good_answer (sous_question_id, corps, commentaire) VALUES (?, ?, ?)";
                        $this->db->query($sql, array(
                            $idInsertion,
                            $_POST["complement"],
                            $_POST["commentaire"]
                        ));
                    }

                    else{
                        $sql = "UPDATE sub_question SET corps = ? WHERE id = ?";
                        $this->db->query($sql, array(
                            $_POST["corps"],
                            $idAssociation
                        ));

                        $sql = "UPDATE good_answer SET corps = ?, commentaire = ? WHERE sous_question_id = ?";
                        $this->db->query($sql, array(
                            $_POST["complement"],
                            $_POST["commentaire"],
                            $idAssociation
                        ));
                    }

                    $sql = "SELECT exercise.id FROM exercise LEFT JOIN question ON exercise.id = question.exercice_id WHERE question.id = ?";
                    $exercice = $this->db->query($sql, $id)->result()[0];

                    
                    redirect($this->session->LHS . "admin/questions-liste/" . $exercice->id, 'location', 301);
                }

                $this->load->view('templates/header');
                $this->load->view('exerciseur/association', array(
                    "id" => $id,
                    "donneesFormulaire" => $donneesFormulaire,
                    "question" => $question[0]
                ));
                $this->load->view('templates/footer');
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function vraiOuFaux($id, $idVraiOuFaux)
    {
        if($this->authentification->permission("A")){
            $sql = "SELECT question.id as id, corps, type_question.nom as type FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE question.id=?";
            $question = $this->db->query($sql, $id)->result();
            
            if(isset($question[0])){
                $this->load->database();
                $this->form_validation->set_rules('corps', 'Text', 'required');
                $this->form_validation->set_rules('commentaire', 'Text');
                $this->form_validation->set_rules('vraiOuFaux', 'Radio');

                $donneesFormulaire = array();
                if($idVraiOuFaux !== "0"){
                    $sql = "SELECT sub_question.*, good_answer.corps AS complement, good_answer.commentaire FROM sub_question LEFT JOIN good_answer ON sub_question.id = good_answer.sous_question_id WHERE sub_question.id = ?";
                    $donneesFormulaire = $this->db->query($sql, $idVraiOuFaux)->result();
                    
                    if(empty($donneesFormulaire)){
                        show_404();
                    }
                }

                if ($this->form_validation->run() === True)
                {
                    if(empty($donneesFormulaire)){
                        $sql = "INSERT INTO sub_question (question_id, corps) VALUES (?, ?)";
                        $this->db->query($sql, array(
                            $id,
                            $_POST["corps"],
                        ));
                        $idInsertion = $this->db->insert_id();


                        $sql = "INSERT INTO good_answer (sous_question_id, verite, commentaire) VALUES (?, ?, ?)";
                        $this->db->query($sql, array(
                            $idInsertion,
                            $_POST["vraiOuFaux"],
                            $_POST["commentaire"]
                        ));
                    }

                    else{
                        $sql = "UPDATE sub_question SET corps = ? WHERE id = ?";
                        $this->db->query($sql, array(
                            $_POST["corps"],
                            $idVraiOuFaux
                        ));

                        $sql = "UPDATE good_answer SET verite = ?, commentaire = ? WHERE sous_question_id = ?";
                        $this->db->query($sql, array(
                            $_POST["vraiOuFaux"],
                            $_POST["commentaire"],
                            $idVraiOuFaux
                        ));
                    }

                    $sql = "SELECT exercise.id FROM exercise LEFT JOIN question ON exercise.id = question.exercice_id WHERE question.id = ?";
                    $exercice = $this->db->query($sql, $id)->result()[0];

                    redirect($this->session->LHS . "admin/questions-liste/" . $exercice->id, 'location', 301);
                }

                $this->load->view('templates/header');
                $this->load->view('exerciseur/vraiOuFaux', array(
                    "id" => $id,
                    "donneesFormulaire" => $donneesFormulaire,
                    "question" => $question[0]
                ));
                $this->load->view('templates/footer');
            }
            
            else{
                show_404();
            }
        }
    }
    
    public function texteATrou($id)
    {
        if($this->authentification->permission("A")){
            $sql = "SELECT question.id as id, corps, type_question.nom as type FROM question LEFT JOIN type_question ON question.type_id = type_question.id WHERE question.id=?";
            $question = $this->db->query($sql, $id)->result();
            
            if(isset($question[0])){
                $this->load->database();
                $this->form_validation->set_rules('corps', 'Text', 'required');
                $this->form_validation->set_rules('commentaire', 'Text');

                $donneesFormulaire = array();

                $sql = "SELECT texteATrou FROM question WHERE id = ?";
                $donneesFormulaire = $this->db->query($sql, $id)->result();


                if ($this->form_validation->run() === True)
                {
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

                    $confirmation = $this->trou->transformer($_POST["corps"], $id);

                    if($confirmation){
                        $sql = "SELECT exercise.id FROM exercise LEFT JOIN question ON exercise.id = question.exercice_id WHERE question.id = ?";
                        $exercice = $this->db->query($sql, $id)->result()[0];

                        redirect($this->session->LHS . "admin/questions-liste/" . $exercice->id, 'location', 301);
                    }

                    else{
                        
                        // Message d'erreur
                    }


                }

                $sql = "SELECT count(id) as nombre FROM sub_question WHERE question_id = ?";
                $etat = $this->db->query($sql, $id)->result();

                $this->load->view('templates/header');
                $this->load->view('exerciseur/texteATrou', array(
                    "id" => $id,
                    "donneesFormulaire" => $donneesFormulaire,
                    "etat" => $etat,
                    "question" => $question[0]
                ));
                $this->load->view('templates/footer');
            }
            
            else{
                show_404();
            }
        }
        
    }
    
    public function supprimerSousQuestion($id)
    {
        if($this->authentification->permission("A")){
            $sql = "SELECT id FROM sub_question WHERE id=?";
            $question = $this->db->query($sql, $id)->result();
            
            if(isset($question[0])){
                $this->load->database();

                $sql = "DELETE good_answer FROM good_answer LEFT JOIN sub_question ON good_answer.sous_question_id = sub_question.id WHERE sub_question.id = ?";
                $this->db->query($sql, $id);

                $sql = "DELETE FROM sub_question WHERE id = ?";
                $this->db->query($sql, $id);

                $sql = "SELECT exercise.id FROM exercise LEFT JOIN question ON exercise.id = question.exercice_id LEFT JOIN sub_question ON question.id = sub_question.question_id WHERE question.id = ?";
                $exercice = $this->db->query($sql, $id)->result()[0];
                redirect($this->session->LHS . "admin/questions-liste/" . $exercice, 'location', 301);
            }
            
            else{
                show_404();
            }
        }
    }
}