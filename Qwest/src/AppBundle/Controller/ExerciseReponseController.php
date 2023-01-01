<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TrainDiscussion;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Question;
use AppBundle\Entity\Answer;
use AppBundle\Entity\User;
use AppBundle\Entity\Message;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\GoodAnswerRepository;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Form\AnswerType;
use AppBundle\Form\AnswerGroupType;
use AppBundle\Form\AnswerGroupVraiOuFauxType;
use AppBundle\Form\CarteATrouResponseType;
use AppBundle\Form\AnswerTexteATrouType;
use AppBundle\Form\MessageType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


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

class ExerciseReponseController extends Controller
{
    
    /**
     * @Route("exercice/{id}", name="exercice-inscrits", requirements={
     *         "id": "\d*"
     *     })
     */
    public function exerciceAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        $billetService = $this->container->get(BilletService::class);
        $captchaService = $this->container->get(CaptchaService::class);
        $enregistrementService = $this->container->get(EnregistrementService::class);
        $flash = $request->getSession();
        
        $pConnecte = $utilisateurService->permissionUser();
        $pAdmin = $utilisateurService->permissionAdmin(true);
        
        if($pConnecte || $pAdmin)
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryAnswer = $this->getDoctrine()->getManager()->getRepository(Answer::class);
            $repositoryGoodAnswer = $this->getDoctrine()->getManager()->getRepository(GoodAnswer::class);

            $answer = new Answer();
            $exercice = $repository->findOneById($id);
            
            $layout = $utilisateurService->getLayout();
            
            if($exercice !== null)
            {
                $questionListe = $repositoryQuestion->findQuestionOrderByNumeroOrdre($id);
                $iteration = 0;

                foreach($questionListe as $questionInutile)
                { 
                    $iteration++; 
                }

                if(0 !== $iteration)
                {
                    $questionService->initVarSession($id, $questionListe);
                    
                    $session = $request->getSession();
                    
                    $tableauSession = $session->get("questionActuel");
                    $debut = $tableauSession["debut"];

                    if($pConnecte){
                        if($exercice->getPublie() === false){
                            $flash->getFlashBag()->add("erreur", "Cet exercice est en cours de modification.");
                            
                    
                            return $this->redirectToRoute("exercice-liste-connecte", array("matiere" => "tous-les-exercices"));
                        }
                    }

                    if($debut === true)
                    {
                        $session->remove("enregistre");
                        $session->remove("enregistrement");
                        
                        if($pConnecte){

                            $formBuilder = $this->createFormBuilder()
                                ->add("pseudo", TextType::class)
                                ->add("envoyer", SubmitType::class)
                            ;

                            $form = $formBuilder->getForm();
                            $form->handleRequest($request);
                            
                            if($form->isSubmitted()){
                                if ($form->isValid() && $session->get("nbCaptcha") >= 5) {
                                    if($captchaService->captchaverify($request->get('g-recaptcha-response')))
                                    {
                                        $tableauSession = $session->get("questionActuel");
                                        $questionSession = $tableauSession["question"];
                                        $iterationMax = $tableauSession["iterationMax"];
                                        $pageNombreSession = $tableauSession["pageNombre"];
                                        $pageSession = $tableauSession["page"];

                                        $initialisationSession = array("exercice" => $id, "question" => $questionSession, "iterationMax" => $iterationMax, "debut" => false, "groupePasses" => array(), "pageNombre" => $pageNombreSession, "page" => $pageSession);
                                        $session->set("nbCaptcha", 0);
                                        $session->set("questionActuel", $initialisationSession);

                                        return $this->redirectToRoute("exercice-passerelle", array("id" => $id));
                                    }

                                    else
                                    {
                                        $flash->getFlashBag()->add("erreur", "Le captcha n'a pas permi de vous identifier en tant qu'humain.");


                                        return $this->redirectToRoute("exercice-passerelle", array("id" => $id));
                                    }
                                }
                            }
                            

                            elseif( $session->get("nbCaptcha") < 5)
                            {
                                $tableauSession = $session->get("questionActuel");
                                $tableauSession["debut"] = false;
                                $session->set("questionActuel", $tableauSession);
                            }

                            $repositoryAnswer->findAndDeleteAnswer();
                            
                            return $this->render('@App/Exercice/introExercice.html.twig', array(
                                "exercice" => $exercice,
                                "form" => $form->createView(),
                                "captcha" => $captchaService,
                                "layout" => $layout
                            ));
                        }
                        
                        elseif($pAdmin)
                        {
                            $tableauSession = $session->get("questionActuel");
                            $questionSession = $tableauSession["question"];
                            $iterationMax = $tableauSession["iterationMax"];
                            $pageNombreSession = $tableauSession["pageNombre"];
                            $pageSession = $tableauSession["page"];

                            $initialisationSession = array("exercice" => $id, "question" => $questionSession, "iterationMax" => $iterationMax, "debut" => false, "groupePasses" => array(), "pageNombre" => $pageNombreSession, "page" => $pageSession);
                            $session->set("questionActuel", $initialisationSession);
                            
                            $repositoryAnswer->findAndDeleteAnswer();
                            
                            return $this->render('@App/Exercice/introExercice.html.twig', array(
                                "exercice" => $exercice,
                                "layout" => $layout
                            ));
                        }
                        
                    }
                    
                    return $this->redirectToRoute("exercice-passerelle", array("id" => $id));
                    
//                    return $this->redirectToRoute("", array("id" => $exercice->getId()));

//                    initialisation des variables pour la suite:

                    $session = $request->getSession();
                    $tableauSession = $session->get("questionActuel");
                    $questionsSession = $tableauSession["question"];
                    $iterationMaxSession = $tableauSession["iterationMax"];

//                    Recherche des questions:

                    $questionTab = $questionService->rechercheQuestions($questionListe);

                    $iteration = 0;

                    $utilisateurPersist = $repositoryUser->findOneById($session->get("id"));
                    
                    while($iteration !== $questionsSession)
                    {
                        if($questionsSession >= $iterationMaxSession)
                        {
                            $session->remove("questionActuel");
                            
                            return $this->redirectToRoute("calcul-resultats", array("id" => $id));
                        }

                        $iteration += 1;

                        $QuetionTabCase = $questionTab[$iteration];
                        $question = $repositoryQuestion->findOneById($QuetionTabCase["id"]);
                    }

                    
//                    Création du formulaire:

                    
                    $answer->setDateCreation(new \Datetime());
                    $answer->setQuestion($question);
                    $answer->setUtilisateur($utilisateurPersist);
                    
                    
//                    Questions simples (création des formulaires)
                    
                    
                    if($question->getType()->getNom() === "Question à trou")
                    {
                        $form = $this->createForm(AnswerType::class, $answer);
                        $form->handleRequest($request);
                    }
                    
                    elseif($question->getType()->getNom() === "Quizz"){
                        $formBuilder = $this->createFormBuilder($answer);
                        $formBuilder
                            ->add("reponseQuizz", EntityType::class, array(
                                'class'    => GoodAnswer::class,
                                'multiple' => true,
                                "expanded" => true,
                                "query_builder" => function(GoodAnswerRepository $repo) use($question){
                                    return $repo->getPropositionsByQuestion($question->getId());
                                }
                            ))
                            ->add("envoyer", SubmitType::class)
                        ;
                        
                        $form = $formBuilder->getForm();
                        $form->handleRequest($request);
                    }
                    
                    elseif($question->getType()->getNom() === "Carte à trou"){
                        foreach($question->getSubQuestion() as $sousQuestion){
                            $reponseTemp = new Answer();
                            $reponseTemp->setDateCreation(new \DateTime());
                            $sousQuestion->addReponse($reponseTemp);
                            
                        }
                        
                        $form = $this->createForm(CarteATrouResponseType::class, $question);
                        $form->handleRequest($request);
                    }
                    
                    
//                    Validation du formulaire et enregistrement temporaire en session
                    if($question->getType()->getNom() === "Question à trou" || $question->getType()->getNom() === "Quizz" || $question->getType()->getNom() === "Carte à trou"){
                        if($form->isSubmitted()){
                            if(($question->getType()->getNom() === "Question à trou" || $question->getType()->getNom() === "Quizz") && $form->isValid()){
                                $enregistrementService->mettreEnSession($answer, $question->getId());
                                $questionService->rafraichissementSession();
                                return $this->redirectToRoute("exercice-inscrits", array("id" => $id));
                            }

                            if($question->getType()->getNom() === "Carte à trou" && $form->isValid()){
                                $enregistrementService->mettreEnSession($answer, $question->getId());
                                $questionService->rafraichissementSession();
                                return $this->redirectToRoute("exercice-inscrits", array("id" => $id));
                            }
                        }
                        
                    }
                    
                    $ressources = $ressourceService->createRessource($question);
                    
                    
//                    Affichage des rendus pour les questions simples
                    
                    
                    if($question->getType()->getNom() === "Question à trou")
                    {
                        return $this->render('@App/Exercice/exerciceATrou.html.twig', array(
                            "form" => $form->createView(),
                            "session" => $session->get("questionActuel"),
                            "question" => $question,
                            "exercice" => $exercice,
                            "iterationMaxSession" => $iterationMaxSession,
                            "ressources" => $ressources,
                            "layout" => $layout,
                        ));
                    }
                    
                    elseif($question->getType()->getNom() === "Quizz"){
                        return $this->render('@App/Exercice/exerciceQuizz.html.twig', array(
                            "form" => $form->createView(),
                            "session" => $session->get("questionActuel"),
                            "question" => $question,
                            "exercice" => $exercice,
                            "iterationMaxSession" => $iterationMaxSession,
                            "ressources" => $ressources,
                            "layout" => $layout,
                            "ressourceService" => $ressourceService,
                        ));
                        
                    }
                    
                    elseif($question->getType()->getNom() === "Carte à trou"){
                        $ressources = $ressourceService->createRessource($question);
                        
                        return $this->render('@App/Exercice/exerciceCarteATrou.html.twig', array(
                            "form" => $form->createView(),
                            "session" => $session->get("questionActuel"),
                            "question" => $question,
                            "exercice" => $exercice,
                            "iterationMaxSession" => $iterationMaxSession,
                            "ressources" => $ressources,
                            "layout" => $layout,
                            "ressourceService" => $ressourceService
                        ));
                    }
                    
                    
//                    Question groupée
                    
                    
                    elseif($question->getType()->getNom() === "Association" || $question->getType()->getNom() === "Vrai ou faux" || $question->getType()->getNom() === "Texte à trou"){

                        $em = $this->getDoctrine()->getManager();
                        $groupe = $question->getGroupe();
                        $typeQuestion = $question->getType();
                        
                        $questionListe = $repositoryQuestion->findBy(
                            array(
                                "groupe" => $groupe,
                                "type" => $typeQuestion
                            )
                        );
                        
                        $nombreQuestion = 0;
                        
                        foreach($questionListe as $questionBoucle){
                            $nombreQuestion++;
                        }
                        
                        $i = 0;
                        $questionTableau = array();
                        
                        foreach($questionListe as $questionListeBoucle)
                        {
                            $questionTableau[$i] = $questionListeBoucle;
                            $i++;
                        }
                        
                        $groupeTemp = new AssociationGroup();
                        
                        for ($i = 0; $i < $nombreQuestion; $i++){
                            $answerBoucle = new Answer();
                            $answerBoucle->setQuestion($questionTableau[$i]);
                            $answerBoucle->setDateCreation(new \DateTime());
                            $answerBoucle->setGroupe($groupeTemp);
                            $answerBoucle->setUtilisateur($utilisateurPersist);
                            $answerTableau[$i] = $answerBoucle;
                            $groupeTemp->addReponseUtilisateur($answerTableau[$i]);
                        }
                        
                        $session->set("groupe", $groupe);
                        
                        if($question->getType()->getNom() === "Association"){
                            $form = $this->createForm(AnswerGroupType::class, $groupeTemp);
                        }
                        
                        elseif($question->getType()->getNom() === "Vrai ou faux"){
                            $form = $this->createForm(AnswerGroupVraiOuFauxType::class, $groupeTemp);
                        }
                        
                        elseif($question->getType()->getNom() === "Texte à trou"){
                            $form = $this->createForm(AnswerTexteATrouType::class, $groupeTemp);
                        }

                        
                        $form->handleRequest($request);
                        if($form->isSubmitted()){
                            if ($form->isValid()) {
                                foreach($groupeTemp->getReponseUtilisateur() as $reponseUtilisateur)
                                {
                                    $groupe->addReponseUtilisateur($reponseUtilisateur);
                                    $reponseUtilisateur->setGroupe($groupe);
                                    $enregistrementService->mettreEnSession($reponseUtilisateur, $question->getId());
                                }

                                unset($groupeTemp);

    //                            $em->persist($groupe);
    //                            $em->flush();

                                $questionService->setGroupePasse($groupe);
                                $questionService->rafraichissementSession();


                                return $this->redirectToRoute("exercice-inscrits", array("id" => $id));
                            }
                        }
                        
                        $ressources = $ressourceService->createRessource($question->getGroupe());
                        
                        if($question->getType()->getNom() === "Association"){
                            return $this->render('@App/Exercice/exerciceAssociation.html.twig', array(
                                "form" => $form->createView(),
                                "session" => $session->get("questionActuel"),
                                "question" => $question,
                                "exercice" => $exercice,
                                "iterationMaxSession" => $iterationMaxSession,
                                "ressources" => $ressources,
                                "layout" => $layout,
                                "ressourceService" => $ressourceService,
                            ));
                        }
                        
                        elseif($question->getType()->getNom() === "Vrai ou faux"){
                            return $this->render('@App/Exercice/exerciceVraiOuFaux.html.twig', array(
                                "form" => $form->createView(),
                                "session" => $session->get("questionActuel"),
                                "question" => $question,
                                "exercice" => $exercice,
                                "iterationMaxSession" => $iterationMaxSession,
                                "ressources" => $ressources,
                                "layout" => $layout,
                            ));
                        }
                        
                        elseif($question->getType()->getNom() === "Texte à trou"){
                            return $this->render('@App/Exercice/exerciceTexteATrou.html.twig', array(
                                "form" => $form->createView(),
                                "session" => $session->get("questionActuel"),
                                "question" => $question,
                                "exercice" => $exercice,
                                "iterationMaxSession" => $iterationMaxSession,
                                "ressources" => $ressources,
                                "layout" => $layout,
                                "groupe" => $groupe,
                            ));
                        }
                        
                    }

//                    Question ouverte
                    
                    elseif($question->getType()->getNom() === "Ouverte"){
                        $ressources = $ressourceService->createRessource($question);


                        if($pConnecte){
                            $fil = new TrainDiscussion();

                            $message = new Message();
                            $message->setType("R");
                            $message->setFil($fil);
                            $message->setQuestion($question);
                            $message->setLu(false);
                            $message->setDateCreation(new \Datetime());

                            $form = $this->createForm(MessageType::class, $message);
                            $form->handleRequest($request);

                            if($form->isSubmitted()){
                                if ($form->isValid()) {
                                    $destinataire = $repositoryUser->findOneByPermission("A");
                                    $eleve = $repositoryUser->findOneById($session->get("id"));
                                    $message->setDestinataire($destinataire);
                                    $message->setEnvoyeur($eleve);
                                    $message->setContenu($billetService->transformationTexte($message->getContenu()));

                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($message);
                                    $em->flush();

                                    $questionService->rafraichissementSession();


                                    return $this->redirectToRoute("exercice-inscrits", array("id" => $id));
                                }
                            }

                            return $this->render('@App/Exercice/exerciceOuverteConnecte.html.twig', array(
                                "form" => $form->createView(),
                                "session" => $session->get("questionActuel"),
                                "question" => $question,
                                "exercice" => $exercice,
                                "ressources" => $ressources,
                                "iterationMaxSession" => $iterationMaxSession,
                                "layout" => $layout,
                                "ressourceService" => $ressourceService,
                            ));
                        }

                        elseif($pAdmin)
                        {
                            if($question->getNumeroOrdre() === $iterationMaxSession + 1)
                            {
                                $placementQuestion = "D";
                            }

                            else{
                                $placementQuestion = "AD";
                            }
                            $questionService->rafraichissementSession();

                            return $this->render('@App/Exercice/exerciceOuverteAdmin.html.twig', array(
                                "session" => $session->get("questionActuel"),
                                "question" => $question,
                                "exercice" => $exercice,
                                "ressources" => $ressources,
                                "iterationMaxSession" => $iterationMaxSession,
                                "layout" => $layout,
                                "ressourceService" => $ressourceService,
                                "placementQuestion" => $placementQuestion,
                            ));
                        }
                    }
                }

                else
                {
                    $flash->getFlashBag()->add("erreur", "Il n'y a pas de question, ou du moins elles n'ont pas été trouvées!");
                    
                    return $this->redirectToRoute("exercice-liste-admin", array("id" => 0));
                }
            }

            else
            {
                $flash->getFlashBag()->add("erreur", "L'exercice est introuvable.");
                    
                return $this->redirectToRoute("exercice-liste-admin", array("id" => 0));
            }  
        }
        
        else{  
            return $this->redirectToRoute("accueil");
        }   
    }
}