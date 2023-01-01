<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Question;
use AppBundle\Entity\Answer;
use AppBundle\Entity\Points;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Service\BilletService;
use AppBundle\Service\UtilisateurService;
use AppBundle\Service\CalculResultatsService;
use AppBundle\Service\CaptchaService;
use AppBundle\Service\QuestionService;
use AppBundle\Service\RessourceService;
use AppBundle\Service\TexteATrouService;
use AppBundle\Service\EnregistrementService;

class CalculResultatsController extends Controller
{
    /**
    * @Route("/exercice/resultats/{id}", name="calcul-resultats", requirements={
    *         "id": "\d*"
    *     })
    */
    public function calculResultatsAction(Request $request, $id)
    {
        
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $calculResultatsService = $this->container->get(CalculResultatsService::class);
        $enregistrementService = $this->container->get(EnregistrementService::class);
        
        if($utilisateurService->permissionAdmin(true) || $utilisateurService->permissionUser())
        {
            
            $session = $request->getSession();
            $repositoryAnswer = $this->getDoctrine()->getManager()->getRepository(Answer::class);
            $repositoryPoints = $this->getDoctrine()->getManager()->getRepository(Points::class);
            $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            
            $session->remove("questionActuel");
            $enregistrementService->enregistrerEnBase();
                
            $reponses = $repositoryAnswer->findAnswerByExerciceAndUser($id);
            $points = $repositoryPoints->findPointsByExerciceAndUser($id);
            $exercice = $repositoryExercise->findOneById($id);
            
            $layout = $utilisateurService->getLayout();
            
            if($repositoryAnswer->findAnswerOrNot($id) === false)
            {
                if($utilisateurService->permissionAdmin(true))
                {
                    
                    return $this->redirectToRoute("question-liste", array(
                        "id" => $id
                    ));
                }
                else{
                    return $this->redirectToRoute("accueil-connecte", array(
                        "id" => $id
                    ));
                }

            }
            
//            Voici la fonction:
            
            if($session->get("enregistre") === null){
                $tabPoints = $calculResultatsService->CalculReponsesQuestionsSimples($id);
                $reponses = $repositoryAnswer->findAnswerByExerciceAndUser($id);
                $session->set("enregistre", $tabPoints);
                $session->set("nbCaptcha", $session->get("nbCaptcha") + 1);
            }
            
            else{
                $tabPoints = $session->get("enregistre");
            }
            
            return $this->render('@App/Exercice/calculResultats.html.twig', array(
                "reponses" => $reponses,
                "points" => $tabPoints,
                "exercice" => $exercice,
                "pointsAncien" => $points,
                "id" => $id,
                "calculResultatsService" => $calculResultatsService,
                "layout" => $layout,
            ));
            
        } 
        
        else{
            return $this->redirectToRoute("accueil");
        }
    }
} 