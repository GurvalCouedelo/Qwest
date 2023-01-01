<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\Answer;
use AppBundle\Entity\Question;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\AssociationGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;

class QuestionService extends Controller
{
    public function initVarSession($id, $questionListe)
    {
        $repository2 = $this->getDoctrine()->getManager()->getRepository(Question::class);
        $repository4 = $this->getDoctrine()->getManager()->getRepository(Answer::class);
        
        $initialisationSession = array("exercice" => $id, "question" => 1, "iterationMax" => 0, "debut" => true, "groupePasses" => array(), "pageNombre" => $repository2->countPageByExercise($id), "page" => 1);
        
        $session = $this->container->get('session');
        $tableauSession = $session->get("questionActuel");

        $exerciceSession = $tableauSession["exercice"];

        if(null === $session->get("questionActuel") || $exerciceSession !== $id) 
        {
            $session->set("questionActuel", $initialisationSession);
            $session->remove("enregistre");
            
            if($repository4->findAnswerOrNot($id) === true)
            {
                $this->suppressionQuestions($id);
            }
        }

        $tableauSession = $session->get("questionActuel");
        $exerciceSession = $tableauSession["exercice"];
        $questionSession = $tableauSession["question"];
        $debutSession = $tableauSession["debut"];
        $groupePassesSession = $tableauSession["groupePasses"];
        $pageNombreSession = $tableauSession["pageNombre"];
        $pageSession = $tableauSession["page"];
                
//        Initialistation de iterationMax:
        
        $iterationMax = 1;
                    
        foreach ($questionListe as $questionBoucle)
        {
            $iterationMax++;
        }
        
        $initialisationSession = array("exercice" => $id, "question" => $questionSession, "iterationMax" => $iterationMax, "debut" => $debutSession, "groupePasses" => $groupePassesSession, "pageNombre" => $pageNombreSession, "page" => $pageSession);
        $session->set("questionActuel", $initialisationSession);
    }
    
    public function verifNumeroOrdre($exerciceId)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
        $em = $this->getDoctrine()->getManager();
        
        $questions = $repository->findQuestionOrderByNumeroOrdre($exerciceId);
        
        $numeroOrdre = 1;
        
        foreach($questions as $questionBoucle)
        {
            $ordreActuel = $questionBoucle->getNumeroOrdre();
            
            if($ordreActuel !== $numeroOrdre)
            {
                $questionTemp = $repository->findOneById($questionBoucle->getId());
                
                if($questionTemp === null){
                    break;
                }
                
                $questionTemp->setNumeroOrdre($numeroOrdre);
                
                $em->persist($questionTemp);
            }
                
            $numeroOrdre++;
        }

        $em->flush();
        
    }
    
    public function rechercheQuestions($questionListe)
    {
        $iteration = 0;

        foreach ($questionListe as $questionBoucle)
        {
            $iteration += 1;
            $questionTab[$iteration] = array("corps" => $questionBoucle->getCorps(), "id" => $questionBoucle->getId(), "ordre" => $questionBoucle->getNumeroOrdre(), "aide" => $questionBoucle->getAide());
        }
        
        return $questionTab;
    }
    
    
    public function suppressionQuestions($id)
    {
        $repositoryAnswer = $this->getDoctrine()->getManager()->getRepository(Answer::class);
        $reponses = $repositoryAnswer->findAnswerByExerciceAndUser($id);
        $em = $this->getDoctrine()->getManager();
        
        foreach($reponses as $reponsesBoucle)
        {
            $remove = $repositoryAnswer->findOneById($reponsesBoucle->getId());
            $em->remove($remove);
        }
        
        $em->flush();
    }
    
    public function rafraichissementSession()
    {
        $session = $this->container->get('session');
        $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
        $repositoryExercice = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
        
        $tableauSession = $session->get("questionActuel");
        $questionPlus1 = $tableauSession["question"] + 1;
        $exerciceSession = $tableauSession["exercice"];
        $iterationMaxSession = $tableauSession["iterationMax"];
        $groupePassesSession = $tableauSession["groupePasses"];
        $pageNombreSession = $tableauSession["pageNombre"];
        $pageSession = $tableauSession["page"];

        $exercice = $repositoryExercice->findOneById($tableauSession["exercice"]);
        $question = $repositoryQuestion->findOneBy(array("numeroOrdre" => $questionPlus1, "exercice" => $exercice));
        
        if($question === null){
            $sessionPlus1 = array("exercice" => $exerciceSession, "question" => $questionPlus1, "iterationMax" => $iterationMaxSession, "debut" => false, "groupePasses" => $groupePassesSession, "pageNombre" => $pageNombreSession, "page" => $pageSession);
            $session->set("questionActuel", $sessionPlus1);
            return;
        }
        
        else{
            $groupe = $question->getGroupe();
        }
        
        if($groupe !== null){
            $groupe = $groupe->getId();
        }
        
        while($groupe !== null && in_array($groupe, $tableauSession["groupePasses"]))
        {
            $questionPlus1++;
            
            if($questionPlus1 === $tableauSession["iterationMax"])
            {
                break;
            }
            
            $question = $repositoryQuestion->findOneBy(array("numeroOrdre" => $questionPlus1, "exercice" => $exercice));
            
            $groupe = $question->getGroupe();
            
            if($groupe !== null){
                $groupe = $groupe->getId();
            }
            
            else{
                break;
            }
        }
        
        $pagePlus1 = $pageSession + 1;

        $sessionPlus1 = array("exercice" => $exerciceSession, "question" => $questionPlus1, "iterationMax" => $iterationMaxSession, "debut" => false, "groupePasses" => $groupePassesSession, "pageNombre" => $pageNombreSession, "page" => $pagePlus1);
        $session->set("questionActuel", $sessionPlus1);
    }
    
    public function setGroupePasse($groupe)
    {
        $session = $this->container->get('session');
        $questionActuel = $session->get("questionActuel");
        
        $groupePasses = $questionActuel["groupePasses"];
        array_push($groupePasses, $groupe->getId());
        
        $initialisationSession = array("exercice" => $questionActuel["exercice"], "question" => $questionActuel["question"], "iterationMax" => $questionActuel["iterationMax"], "debut" => $questionActuel["debut"], "groupePasses" => $groupePasses, "pageNombre" => $questionActuel["pageNombre"], "page" => $questionActuel["page"]);
        $session->set("questionActuel", $initialisationSession);
    }
    
    public function groupeBasique($exercice)
    {
        
        $groupe = new AssociationGroup();
        $groupe->setExercice($exercice);
        $groupe->setDescription("Sans consigne");
            
        $em = $this->getDoctrine()->getManager();
        $em->persist($groupe);
        $em->flush();
            
        return $groupe;
    }
    
    public function questionListeRedirection()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);
        $session = $this->container->get('session');
        
        $user = $repository->findOneBy(
            array("id" => $session->get("id"))
        );
        
        if(!empty($session->get("derniere-page")) && !empty($session->get("derniere-page-parametre")))
        {
            return $this->redirectToRoute($session->get("derniere-page"), array(
                "id" => $session->get("derniere-page-parametre")                  
            ));
        }
        
        else{
            if($user->getPermission() === "A"){
                return $this->redirectToRoute("exercice-liste-admin", array("id" => 0));
            }
            
            elseif($user->getPermission() === "U"){
                return $this->redirectToRoute("exercice-liste-connecte", array("id" => 0));
            }
            
        }
    }
    
    public function getLastQuestionGroup($groupe)
    {
        $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
        return $repositoryQuestion->findOneBy(array("groupe" => $groupe->getId()), array("numeroOrdre" => "desc"), 1);
    }

    public function publierExercice($id, $ferme = true)
    {
        $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);

        $exercice = $repositoryExercise->findOneById($id);

        if ($ferme === false){
            if ($exercice->getPublie() === false) {
                $exercice->setPublie(true);
            } else {
                $exercice->setPublie(false);
            }
        }
        else{
            $exercice->setPublie(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($exercice);
        $em->flush();

        return $this->redirectToRoute("question-liste", array(
            "id" => $id
        ));

    }
}