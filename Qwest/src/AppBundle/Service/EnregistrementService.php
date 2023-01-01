<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\TypeNote;
use AppBundle\Entity\Answer;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\Question;
use AppBundle\Entity\AssociationGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;

class EnregistrementService extends Controller
{
    public function mettreEnSession($answer, $numeroQuestion)
    {
        $session = $this->container->get('session');
        
        $listeReponseQuizz = array();
        
        foreach($answer->getReponseQuizz() as $quizzBoucle){
            if($quizzBoucle !== null){
                array_push($listeReponseQuizz, $quizzBoucle);
            }
        }
        
        $reponseAssoc = null;
        
        if($answer->getReponseAssoc() !== null){
            $reponseAssoc = $answer->getReponseAssoc()->getId();
        }
        
        $groupe = null;
        
        if($answer->getGroupe() !== null){
            $groupe = $answer->getGroupe()->getId();
        }
        
        $capsule = array(
            "corps" => $answer->getCorps(),
            "question" => $numeroQuestion,
            "reponseQuizz" => $listeReponseQuizz,
            "verite" => $answer->getVerite(),
            "reponseAssoc" => $reponseAssoc,
            "groupe" => $groupe
            
        );
        
        unset($answer);
        
        if($session->get("enregistrement") === null){
            $session->set("enregistrement", array($capsule));
        }
        
        else{
            $sessionActuelle = $session->get("enregistrement");
            array_push($sessionActuelle, $capsule);
            
            $session->set("enregistrement", $sessionActuelle);
            
        }
    }
    
    public function enregistrerEnBase()
    {
        $session = $this->container->get('session');
        
        if($session->get("enregistrement") !== null){
            $reponses = $session->get("enregistrement");
            
            $em = $this->getDoctrine()->getManager();
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryGoodAnswer = $this->getDoctrine()->getManager()->getRepository(GoodAnswer::class);
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            
            $utilisateur = $repositoryUser->findOneById($session->get("id"));
            
            foreach($reponses as $reponseBoucle){
                $answerTemp = new Answer();
                
                if($reponseBoucle["reponseQuizz"] !== null){
                    foreach($reponseBoucle["reponseQuizz"] as $quizzBoucle){
                        $reponseTemp = $repositoryGoodAnswer->findOneById($quizzBoucle);
                        $answerTemp->addReponseQuizz($reponseTemp);
                    }
                }
                
                if($reponseBoucle["reponseAssoc"] !== null){
                    $reponseTemp = $repositoryGoodAnswer->findOneById($reponseBoucle["reponseAssoc"]);
                    $answerTemp->setReponseAssoc($reponseTemp);
                }
                
                if($reponseBoucle["groupe"] !== null){
                    $groupeTemp = $repositoryAssociationGroup->findOneById($reponseBoucle["groupe"]);
                    $answerTemp->setGroupe($groupeTemp);
                }
                
                $answerTemp->setQuestion($repositoryQuestion->findOneById($reponseBoucle["question"]));
                $answerTemp->setCorps($reponseBoucle["corps"]);
                $answerTemp->setUtilisateur($utilisateur);
                $answerTemp->setDateCreation(new \Datetime());
                
                
                $answerTemp->setDateCreation(new \Datetime());
                
                $em->persist($answerTemp);
                
            }
            
            $em->flush();
            
        }
        
    }
    
    
}