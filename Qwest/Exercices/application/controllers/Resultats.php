<?php
class Resultats extends CI_Controller {
    public function index()
    {
        if($this->authentification->permission("C")){
            $sql = 'SELECT question.id, numeroOrdre, corps AS ennonce, type_question.nom AS type FROM question LEFT JOIN type_question on question.type_id = type_question.id WHERE question.exercice_id = ? ORDER BY question.numeroOrdre';
            $questions = $this->db->query($sql, $this->session->exercice)->result();

            $sql = 'SELECT exercise.titre, classroom.id as classeId FROM exercise 
            LEFT JOIN chapter ON exercise.chapitre_id = chapter.id 
            LEFT JOIN classroom ON classroom.id = chapter.classe_id WHERE exercise.id = ?';
            
            $exercice = $this->db->query($sql, $this->session->exercice)->result();

            $correction = $this->calcul->resultats();
            
            if($this->session->enregistre === false){
                $sql = "INSERT INTO points (points, dateCreation, exercice_id, utilisateur_id) VALUES (?, NOW(), ?, ?)";
                $this->db->query($sql, array(
                    $correction["resultat"] / $correction["totalPoints"] * 100,
                    $this->session->exercice,
                    $this->session->id
                ));
                
                $this->session->set_userdata("enregistre", true);
                
            }
            
                
            $sql = 'SELECT COUNT(*) - 1 as limite FROM points LEFT JOIN user ON points.utilisateur_id = user.id LEFT JOIN exercise ON points.exercice_id = exercise.id WHERE user.id = ? AND exercise.id =?';
            $limite = intval($this->db->query($sql, 
                array(
                    $this->session->id,
                    $this->session->exercice
                )
            )->result()[0]->limite);
            
            
            $sql = 'SELECT points.* FROM points LEFT JOIN user ON points.utilisateur_id = user.id LEFT JOIN exercise ON points.exercice_id = exercise.id WHERE user.id = ? AND exercise.id =? LIMIT ?';
            $points = $this->db->query($sql, 
                array(
                    $this->session->id,
                    $this->session->exercice,
                    $limite
                )
            )->result();
            
            

            $this->load->view('templates/header');
            $this->load->view('exercice/resultats', array(
                "questions" => $questions,
                "exercice" => $exercice[0],
                "points" => $points,
                "correction" => $correction,
                "serviceCorrection" => $this->correction
            ));
            $this->load->view('templates/footer');
        }
    }
}