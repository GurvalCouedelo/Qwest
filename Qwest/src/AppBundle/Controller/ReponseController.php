<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Question;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\User;
use AppBundle\Entity\Means;
use AppBundle\Form\GoodAnswerQuizzType;
use AppBundle\Form\GoodAnswerQuizzWithMeansType;
use AppBundle\Form\GoodAnswerQuizzOnlyMeansType;
use AppBundle\Form\GoodAnswerType;
use AppBundle\Form\GoodAnswerTrueOrFalseWithSubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Service\BilletService;
use AppBundle\Service\UtilisateurService;
use AppBundle\Service\CalculResultatsService;
use AppBundle\Service\CaptchaService;
use AppBundle\Service\QuestionService;
use AppBundle\Service\RessourceService;
use AppBundle\Service\TexteATrouService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ReponseController extends Controller
{
    /**
     * @Route("admin/question/creation-reponse/{id}", name="reponse-creation", requirements={
     *         "id": "\d*"
     *     })
     */
    public function reponseCreationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        $billetService = $this->container->get(BilletService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $flash = $request->getSession();
            
            $question = $repository->findOneById($id);
            $bonneReponse = new GoodAnswer();
            
            if($question !== null)
            {
                $bonneReponse->setQuestion($question);
                $question->addBonneReponse($bonneReponse);
                
                if($question->getType()->getNom() === "Question à trou")
                {
                    $formBuilder = $this->createFormBuilder($bonneReponse);
                    $formBuilder
                        ->add("corps", TextType::class)
                        ->add("nbPoint", IntegerType::class)
                        ->add("commentaire", TextType::class)
                        ->add("envoyer", SubmitType::class)
                    ;
                    
                    $form= $formBuilder->getForm();
                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $bonneReponse->setVerite(true);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($bonneReponse);
                        $em->flush();
                        
                        $questionService->publierExercice($bonneReponse->getQuestion()->getExercice()->getId());

                        return $this->redirect($this->generateUrl('question-liste', 
                            array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                        );
                        
                        
                    }

                    return $this->render('@App/Admin/reponseCreationAdmin.html.twig', array(
                        "form" => $form->createView(),
                        "bonneReponse" => $bonneReponse,
                        "question" => $question
                    ));
                }
                
                elseif($question->getType()->getNom() === "Quizz")
                {
                    $ressourceVerifFichier = $ressourceVerifLien = null; 
                    $ressource = new Means();
                    
                    $ressource->addBonneReponse($bonneReponse);
                    $bonneReponse->setRessource($ressource);
                    
                    $form = $this->createForm(GoodAnswerQuizzType::class, $bonneReponse);
                    $form->handleRequest($request);
                    
                    if ($form->isValid()) {
                        if($billetService->verifSiNull($question->getCorps()) && $ressourceVerifFichier === null && $ressourceVerifLien === null ){
                            $flash->getFlashBag()->add("message", "<p class=\"erreur\">Vous devez au moins complèter soit le champ de la consigne, soit le champ de la ressource.</p>"); 
                            
                            return $this->redirectToRoute("reponse-creation", array(
                                "id" => $question->getId(),
                            ));
                        }

                        else{
                            if($ressourceVerifFichier !== null || $ressourceVerifLien !== null)
                            {
                                if($ressourceService->inspectionEnregistrementRessource($ressource) instanceof \StockHG3\appBundle\Entity\Means)
                                {
                                
                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($ressource);
                                    $em->persist($bonneReponse);
                                    $em->persist($question);
                                    $em->flush();
                                    
                                    $questionService->publierExercice($bonneReponse->getQuestion()->getExercice()->getId());

                                    return $this->redirect($this->generateUrl('question-liste', 
                                        array("id" => $bonneReponse->getQuestion()->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                                    );
                                }

                                else{
                                    $flash->getFlashBag()->add("message", "<p class=\"erreur\">" . $ressourceService->inspectionEnregistrementRessource($ressource) . "</p>");
                                    return $this->redirect($this->generateUrl('question-liste', 
                                        array("id" => $bonneReponse->getQuestion()->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                                    );
                                }
                            }
                            
                            else{
                                foreach($question->getRessources() as $ressource)
                                {
                                    if($ressource->getFile() === null && $ressource->getLien() === null)
                                    {
                                        $ressource->removeQuestion($question);
                                        $question->removeRessource($ressource);
                                    }
                                }

                                $bonneReponse->setRessource(null);

                                $em = $this->getDoctrine()->getManager();
                                $em->persist($bonneReponse);
                                $em->persist($question);
                                $em->flush();
                                
                                $questionService->publierExercice($bonneReponse->getQuestion()->getExercice()->getId());

                                return $this->redirect($this->generateUrl('question-liste', 
                                    array("id" => $bonneReponse->getQuestion()->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                                );
                            }
                        }
                    }

                    return $this->render('@App/Admin/reponseQuizzCreationAdmin.html.twig', array(
                        "form" => $form->createView(),
                        "bonneReponse" => $bonneReponse,
                        "question" => $question
                    ));
                }
                
                else{
                    $flash->getFlashBag()->add("erreur", "Erreur sur le type de la question.");
                    return $questionService->questionListeRedirection();
                }
            }
            
            else{
                $flash->getFlashBag()->add("erreur", "La question à laquelle doit correspondre la réponse est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("/admin/question/reponse-modification/{id}", name="reponse-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function reponseModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryGoodAnswer = $this->getDoctrine()->getManager()->getRepository(GoodAnswer::class);
            $flash = $request->getSession();
            
            $bonneReponse = $repositoryGoodAnswer->findOneById($id);
            $question = $bonneReponse->getQuestion();
            
            if($bonneReponse !== null)
            {
                if($bonneReponse->getQuestion()->getType()->getNom() === "Question à trou")
                {
                    $form = $this->createFormBuilder($bonneReponse);
                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();

                        $em->persist($bonneReponse);
                        $em->flush();
                        
                        $questionService->publierExercice($bonneReponse->getQuestion()->getExercice()->getId());

                        return $this->redirect($this->generateUrl('question-liste', 
                            array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                        );
                    }

                    return $this->render('@App/Admin/reponseModificationAdmin.html.twig', array(
                        "form" => $form->createView(),
                        "bonneReponse" => $bonneReponse,
                        "question" => $question,
                    ));
                }
                
                elseif($bonneReponse->getQuestion()->getType()->getNom() === "Quizz")
                {
                    if($bonneReponse->getRessource() === null)
                    {
                        $form = $this->createForm(GoodAnswerQuizzType::class, $bonneReponse);
                    }
                    
                    else{
                        $form = $this->createForm(GoodAnswerTrueOrFalseWithSubmitType::class, $bonneReponse);
                    }
                    
                    $form->handleRequest($request);
                    
                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($bonneReponse);
                        $em->flush();
                        
                        $questionService->publierExercice($bonneReponse->getQuestion()->getExercice()->getId());
                        
                        return $this->redirect($this->generateUrl('question-liste', 
                            array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                        );
                    }

                    return $this->render('@App/Admin/reponseQuizzModificationAdmin.html.twig', array(
                        "form" => $form->createView(),
                        "bonneReponse" => $bonneReponse,
                        "question" => $question,
                    ));
                }
                
                elseif($bonneReponse->getQuestion()->getType()->getNom() === "Association")
                {
                    $flash->getFlashBag()->add("erreur", "La page de modification de réponse ne gère pas les réponses provenants de questions de type \"association\" mais uniquement les questions à trou et les quizzs.");
                    return $questionService->questionListeRedirection();
                }
                
                else{
                    $flash->getFlashBag()->add("erreur", "Erreur sur le type de la question.");
                    return $questionService->questionListeRedirection();
                }
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "La bonne réponse est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/reponse/suppression/{id}", name="reponse-suppression", requirements={
     *         "id": "\d*"
     *     })
     */
    public function reponseSuppressionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(GoodAnswer::class);
            
            $em = $this->getDoctrine()->getManager();
            
            $bonneReponse = $repository->findOneById($id);
            $idRedirect = $bonneReponse->getQuestion()->getExercice()->getId();
            
            if($bonneReponse === null)
            {
                $flash->getFlashBag()->add("erreur", "La bonne réponse n'a pas été trouvée.");
                return $questionService->questionListeRedirection();
            }
            
            if($bonneReponse->getQuestion()->getType()->getNom() === "Association")
            {
                $flash->getFlashBag()->add("erreur", "Cette page ne permet pas de supprimer les propositions de type \"association\".");
                return $questionService->questionListeRedirection();
            }


            $em->remove($bonneReponse);
            $em->flush();
            
            $questionService->publierExercice($bonneReponse->getQuestion()->getExercice()->getId());
            
            return $this->redirectToRoute("question-liste", array("id" => $idRedirect));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}