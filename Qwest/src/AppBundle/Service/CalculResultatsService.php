<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Entity\User;
use AppBundle\Entity\Points;
use AppBundle\Entity\Answer;
use AppBundle\Entity\Question;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\Exercise;

class CalculResultatsService extends Controller
{
    public function CalculReponsesQuestionsSimples($id)
    {
        $session = $this->container->get('session');
        $em = $this->getDoctrine()->getManager();
        $repositoryAnswer = $this->getDoctrine()->getManager()->getRepository(Answer::class);
        $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
        $repositoryGoodAnswer = $this->getDoctrine()->getManager()->getRepository(GoodAnswer::class);
        $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
        $repositoryUtilisateur = $this->getDoctrine()->getManager()->getRepository(User::class);

        $reponses = $repositoryAnswer->findAnswerByExerciceAndUser($id);
        $questions = $repositoryQuestion->findQuestionOrderByNumeroOrdre($id);
        $nombreQuestion = $repositoryQuestion->findQuestionNumber($id);
        $exercice = $repositoryExercise->findOneById($id);
        $utilisateur = $repositoryUtilisateur->findOneById($session->get("id"));

        
        //        Sélection des réponses
  
        $iteration = 0;
        
        foreach($reponses as $reponsesBoucle)
        {
            if($iteration === $nombreQuestion)
            {
                break;
            }
            
            $TabPremRep[$iteration] = $reponsesBoucle;
            $iteration++;
        }
        
        
//        Calcul du total des points
        
        $iteration = 0;
        $maxPoints[] = null;
        $groupePasses = array();
        
        foreach($questions as $questionsBoucle)
        {
            $bonnesReponses = $repositoryGoodAnswer->findByQuestion($questionsBoucle);
            $iteration++;
            
            foreach($bonnesReponses as $bonnesReponsesBoucle)
            {
                if($bonnesReponsesBoucle->getQuestion()->getType()->getNom() === "Question à trou")
                {
                    if($bonnesReponsesBoucle->getQuestion()->getPoints() > 0 && $bonnesReponsesBoucle->getQuestion()->getPoints() !== null)
                    {
                        $maxPoints[$iteration] = $bonnesReponsesBoucle->getQuestion()->getPoints();
                    }
                    
                    elseif(!isset($maxPoints[$iteration]) || isset($maxPoints[$iteration]) && $maxPoints[$iteration] < $bonnesReponsesBoucle->getNbPoint())
                    {
                        $maxPoints[$iteration] = $bonnesReponsesBoucle->getNbPoint();
                    }
                }
                
                elseif($bonnesReponsesBoucle->getQuestion()->getType()->getNom() === "Quizz")
                {
                    if($questionsBoucle->getPoints() === null || $questionsBoucle->getPoints() <= 0)
                    {
                        if($bonnesReponsesBoucle->getVerite() === true)
                        {
                            $maxPoints[$iteration] = $bonnesReponsesBoucle->getNbPoint();
                            $iteration++;
                        }
                    }
                    
                    else{
                        $maxPoints[$iteration] = $questionsBoucle->getPoints();
                        $iteration++;
                        break;
                    }
                    
                }
                
                elseif($bonnesReponsesBoucle->getQuestion()->getType()->getNom() === "Association" || $bonnesReponsesBoucle->getQuestion()->getType()->getNom() === "Vrai ou faux")
                {
                    if(!$questionsBoucle->getGroupe()->getPoints() === null || !$questionsBoucle->getGroupe()->getPoints() <= 0)
                    {
                        if(!in_array($questionsBoucle->getGroupe()->getId(), $groupePasses))
                        {
                            $maxPoints[$iteration] = $bonnesReponsesBoucle->getQuestion()->getGroupe()->getPoints();
                            $groupe = $questionsBoucle->getGroupe()->getId();
                            $iteration++;
                            array_push($groupePasses, $groupe);
                        }
                    }
                    
                    else{
                        $maxPoints[$iteration] = $bonnesReponsesBoucle->getNbPoint();
                        $iteration++;
                    }
                    
                }
                
                elseif($bonnesReponsesBoucle->getQuestion()->getType()->getNom() === "Texte à trou")
                {
                    if(!in_array($questionsBoucle->getGroupe()->getId(), $groupePasses))
                    {
                        $maxPoints[$iteration] = $bonnesReponsesBoucle->getQuestion()->getGroupe()->getPoints();
                        $groupe = $questionsBoucle->getGroupe()->getId();
                        $iteration++;
                        array_push($groupePasses, $groupe);
                    }
                    
                }
                
            }
        }
        
        $totalPoints = array_sum($maxPoints);
        
//        Calcul des résultats et enregistrement
        $pointsFin = $this->coeurCalcul($TabPremRep, $questions);
        
        if($totalPoints !== 0)
        {
            $multiplicateur = 100 / $totalPoints;
            $pourcentage = $multiplicateur * $pointsFin;
        }
        
        else{
            $pourcentage = 0;
        }
        
        $points = new Points();
        $points->setPoints(ceil($pourcentage));
        $points->setDateCreation(new \Datetime());
        $points->setExercice($exercice);
        $points->setUtilisateur($utilisateur);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($points);
        $em->flush();
        
        $tabPoints = array("pointsFin" => $pointsFin, "total" => $totalPoints, "nombreQuestion" => $nombreQuestion, "pourcentage" => ceil($pourcentage));
        
        return $tabPoints;
    }
    
    public function coeurCalcul($reponses, $questions)
    {
//        Initialisation de certaines variables

        $em = $this->getDoctrine()->getManager();
        $points = 0;
        $pointsTemp = 0;
        $groupesPasses = array();
        
//        Début de la boucle des réponses
        
        foreach($reponses as $reponsesBoucle)
        {
            $iteration = 0;
            
//            Début de la boucle des questions
            
            foreach($questions as $questionsBoucle)
            {
                if($questionsBoucle->getId() === $reponsesBoucle->getQuestion()->getId())
                {
//                    Calcul des points
                    
                    $bonneReponses = $questionsBoucle->getBonneReponses();
                    
                    foreach($bonneReponses as $bonneReponsesBoucle)
                    {
                        $iterationQuizz = 0;
                        
                        if($questionsBoucle->getType()->getNom() === "Question à trou")
                        {
                            $bonneReponse = $bonneReponsesBoucle->getCorps();
                            if(preg_match("#$bonneReponse#i", $reponsesBoucle->getCorps()))
                            {
                                if($bonneReponsesBoucle->getQuestion()->getPoints() === 0 && $bonneReponsesBoucle->getQuestion()->getPoints() !== null)
                                {
                                    $pointsTemp = $bonneReponsesBoucle->getNbPoint();
                                }
                                
                                else{
                                    $pointsTemp = $bonneReponsesBoucle->getQuestion()->getPoints();
                                }
                                
                            }
                        }

                        elseif($questionsBoucle->getType()->getNom() === "Quizz")
                        {
                            $bonneReponse = $bonneReponsesBoucle->getId();
                            $reponsesUtilisateur = $reponsesBoucle->getReponseQuizz();

                            foreach($reponsesUtilisateur as $reponsesUtilisateurBoucle)
                            {

                                if($bonneReponse === $reponsesUtilisateurBoucle->getId())
                                {
                                    if($reponsesUtilisateurBoucle->getVerite() === true)
                                    {
                                        $iterationQuizz++;

                                        $reponsesBoucle->setCorrection(true);
                                        $em->persist($reponsesBoucle);
                                        
                                        if($questionsBoucle->getPoints() === null || $questionsBoucle->getPoints() <= 0)
                                        {
                                            $pointsTemp += $bonneReponsesBoucle->getNbPoint();
                                        }
                                        
                                        else{
                                            $pointsTemp = $questionsBoucle->getPoints();
                                        }
                                    }
                                    
                                    elseif($reponsesUtilisateurBoucle->getVerite() === false){
                                        $pointsTemp = 0;
                                        break 2;
                                    }
                                }
                            }
                              
                            if($iterationQuizz === 0 && $bonneReponsesBoucle->getVerite() === true && $questionsBoucle->getPoints() > 0)
                            {
                                $pointsTemp = 0;
                                break;
                            }
                        }
                        
                        elseif($questionsBoucle->getType()->getNom() === "Association")
                        {
                            $bonneReponse = $bonneReponsesBoucle->getId();
                            $reponsesUtilisateur = $reponsesBoucle->getReponseAssoc();
                                
                            if($bonneReponse === $reponsesUtilisateur->getId())
                            {

                                if($questionsBoucle->getGroupe()->getPoints() === null || $questionsBoucle->getGroupe()->getPoints() <= 0)
                                {
                                    $pointsTemp = $bonneReponsesBoucle->getNbPoint();
                                }

                                else{
                                    if(array_key_exists($questionsBoucle->getGroupe()->getId(), $groupesPasses))
                                    {
                                        $groupesPasses[$questionsBoucle->getGroupe()->getId()] = $groupesPasses[$questionsBoucle->getGroupe()->getId()] + 1; 
                                    }
                                        
                                    else{
                                        $groupesPasses[$questionsBoucle->getGroupe()->getId()] = 1;
                                    }
                                }
                            } 
                        }
                        
                        elseif($questionsBoucle->getType()->getNom() === "Vrai ou faux")
                        {
                            $verite = ($reponsesBoucle->getCorps() === "V") ? true : false;
                            
                            if($bonneReponsesBoucle->getVerite() === $verite)
                            {
//                                throw new Exception(var_dump($bonneReponsesBoucle->getVerite() . " " . $verite));
                                if($questionsBoucle->getGroupe()->getPoints() === null || $questionsBoucle->getGroupe()->getPoints() <= 0)
                                {
                                    $pointsTemp = $bonneReponsesBoucle->getNbPoint();
                                }

                                else{
                                    if(array_key_exists($questionsBoucle->getGroupe()->getId(), $groupesPasses))
                                    {
                                        $groupesPasses[$questionsBoucle->getGroupe()->getId()] = $groupesPasses[$questionsBoucle->getGroupe()->getId()] + 1; 
                                    }
                                        
                                    else{
                                        $groupesPasses[$questionsBoucle->getGroupe()->getId()] = 1;
                                    }
                                }
                            } 
                        }
                        
                        elseif($questionsBoucle->getType()->getNom() === "Texte à trou" && $questionsBoucle->getTrouOuListe() === "T")
                        {
                            $bonneReponse = $bonneReponsesBoucle->getCorps();

                            if(preg_match("#$bonneReponse#i", $reponsesBoucle->getCorps()))
                            {
                                if(array_key_exists($questionsBoucle->getGroupe()->getId(), $groupesPasses))
                                {
                                    $groupesPasses[$questionsBoucle->getGroupe()->getId()] += 1; 
                                }

                                else{
                                    $groupesPasses[$questionsBoucle->getGroupe()->getId()] = 1;
                                }
                                
                                break;
                            }
                        }
                        
                        elseif($questionsBoucle->getType()->getNom() === "Texte à trou" && $questionsBoucle->getTrouOuListe() === "L")
                        {
                            $bonneReponse = $bonneReponsesBoucle;
                            $reponsesUtilisateur = $reponsesBoucle->getReponseAssoc();
                                
                            if($bonneReponse->getId() === $reponsesUtilisateur->getId())
                            {
                                if($bonneReponse->getVerite() === true){
                                    if(array_key_exists($questionsBoucle->getGroupe()->getId(), $groupesPasses))
                                    {
                                        $groupesPasses[$questionsBoucle->getGroupe()->getId()] += 1; 
                                    }

                                    else{
                                        $groupesPasses[$questionsBoucle->getGroupe()->getId()] = 1;
                                    }
                                    
                                }
                            } 
                        }
                    }
                    
                    $points += $pointsTemp;
                    $pointsTemp = 0;
                }

                $iteration++;
            }
        }
        
        $tableauPoints = array();
        
        if($groupesPasses !== array())
        {
            $groupesPresents = array();
            
            foreach($questions as $questionsBoucle)
            {
                if($questionsBoucle->getGroupe() !== null)
                {
                    if(array_key_exists($questionsBoucle->getGroupe()->getId(), $groupesPresents))
                    {
                        $groupesPresents[$questionsBoucle->getGroupe()->getId()] = $groupesPresents[$questionsBoucle->getGroupe()->getId()] + 1; 
                    }
                                        
                    else{
                        $groupesPresents[$questionsBoucle->getGroupe()->getId()] = 1; 
                    }
                }
            }
            
            $i = 0;
            $j = 0;
            $k = 0;
            
            foreach($groupesPasses as $groupesPassesCle => $groupesPassesBoucle)
            {
                $i++;
                foreach($groupesPresents as $groupesPresentsCle => $groupesPresentsBoucle)
                {
                    $j++;
                    if($groupesPassesCle === $groupesPresentsCle)
                    {
                        $k++;
                        foreach($questions as $questionsBoucle)
                        {
                        
                            if($questionsBoucle->getGroupe() !== null){
                                if($questionsBoucle->getGroupe()->getId() === $groupesPresentsCle){
                                    $groupePoints = $questionsBoucle->getGroupe()->getPoints();
                                } 
                            }
                        }
                        $tableauPoints[$i] = $groupesPassesBoucle / $groupesPresentsBoucle * $groupePoints;
//                        throw new Exception(var_dump($groupesPasses));
                    }
                }
            }
            
//            throw new Exception($i ." " . $j . " " . $k);
        }
        
        $points += array_sum($tableauPoints);
        $em->flush();
        
        
//        Fin de la fonction
        
        return $points;
    }
    
}