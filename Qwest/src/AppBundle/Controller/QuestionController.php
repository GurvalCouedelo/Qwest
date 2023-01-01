<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Question;
use AppBundle\Entity\TypeQuestion;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Entity\SubQuestion;
use AppBundle\Entity\User;
use AppBundle\Entity\Means;
use AppBundle\Entity\Reverser;
use AppBundle\Form\GoodAnswerType;
use AppBundle\Form\ReverserType;
use AppBundle\Form\QuestionType;
use AppBundle\Form\QuestionAssociationType;
use AppBundle\Form\QuestionAssociationWithMeansType;
use AppBundle\Form\QuestionTrueOrFalseType;
use AppBundle\Form\PropositionUniqueType;
use AppBundle\Form\PropositionsModificationType;
use AppBundle\Form\VraiOuFauxModificationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use AppBundle\Service\BilletService;
use AppBundle\Service\UtilisateurService;
use AppBundle\Service\CalculResultatsService;
use AppBundle\Service\CaptchaService;
use AppBundle\Service\QuestionService;
use AppBundle\Service\RessourceService;
use AppBundle\Service\TexteATrouService;

class QuestionController extends Controller
{
    /**
     * @Route("/admin/exercice/question/modification/{id}", name="question-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function questionModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $billetService = $this->container->get(BilletService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $flash = $request->getSession();
            
            $question = $repository->findOneById($id);
            
            if($question !== null)
            {
                $exercice = $question->getExercice();
                $questionTemp = new Question();

                $questionTemp->setCorps($question->getCorps());
                $questionTemp->setAide($question->getAide());
                $questionTemp->setPoints($question->getPoints());
                $questionTemp->setType($question->getType());

                if(!$question->getType()->getNom() === "Question à trou" && !$question->getType()->getNom() === "Quizz")
                {
                    $flash->getFlashBag()->add("erreur", "Cette page ne gère que les questions de type \"question à trou\" et \"quizz\".");
                    return $questionService->questionListeRedirection();
                }
                
                $form = $this->createForm(QuestionType::class, $questionTemp);
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();

                    $question->setCorps($questionTemp->getCorps());
                    $question->setPoints($questionTemp->getPoints());
                    $question->setAide($questionTemp->getAide());
                    $question->setType($questionTemp->getType());

                    $question->setCorps($billetService->transformationTexte($question->getCorps(), false));

                    if($question->getType()->getNom() === "Association" && $question->getGroupe() === null || $question->getType()->getNom() === "Vrai ou faux " && $question->getGroupe() === null)
                    {
                        $groupe = $questionService->groupeBasique($exercice);
                        $question->setGroupe($groupe);
                    }
                    
                    if($question->getType()->getNom() === "Association" || $question->getType()->getNom() === "Vrai ou faux")
                    {
                        $questionService->publierExercice($question->getExercice()->getId());
                        
                        $i = 0;
                        
                        foreach($question->getBonneReponses() as $questionliste)
                        {
                            $i++;
                        }
                        
                        if($i === 0)
                        {
                            $bonneReponse = new GoodAnswer();
                            $bonneReponse->setCorps("A complèter.");
                            $bonneReponse->setNbPoint(0);
                            $bonneReponse->setVerite(true);
                            $bonneReponse->setQuestion($question);
                            $question->addBonneReponse($bonneReponse);  
                            $em->persist($bonneReponse);
                            $em->flush();
                        }

                        elseif($i > 1){
                            $i = 0;
                            foreach($question->getBonneReponses() as $questionliste){
                                $i++;
                                if($i > 1){
                                    $em->remove($questionliste);
                                }
                            }

                            $em->flush();
                        }
                        
                    }
                    
                    $question->setCorps($billetService->transformationTexte($question->getCorps(), false));

                    $em->persist($question);
                    $em->flush();

                    return $this->redirect($this->generateUrl('question-liste', 
                        array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                    );
                }
                
                return $this->render('@App/Admin/questionModificationAdmin.html.twig', array(
                    "form" => $form->createView(),
                    "question" => $question,
                ));
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "La question est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    /**
     * @Route("admin/question/{id}/suppression", name="question-suppression", requirements={
     *         "id": "\d*"
     *     })
     */
    public function questionSuppressionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $em = $this->getDoctrine()->getManager();
            
            $question = $repository->findOneById($id);
            
            if($question === null)
            {
                $flash->getFlashBag()->add("erreur", "La question n'a pas été trouvée.");
                
                return $questionService->questionListeRedirection();
            }
            
            $exercice = $question->getExercice();
            $exerciceId = $exercice->getId();

            $questionService->publierExercice($question->getExercice()->getId());

            $em->remove($question);
            $em->flush();
            
            $questionService->verifNumeroOrdre($exerciceId);
            
            return $this->redirectToRoute("question-liste", array("id" => $exerciceId));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/question/intervertir/{id}", name="question-intervertir", requirements={
     *         "id": "\d*"
     *     })
     */
    public function questionIntervertirAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        $session = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryExercice = $this->getDoctrine()->getManager()->getRepository("AppBundle:Exercise");
            
            $reverser = new Reverser();
            $exercice = $repositoryExercice->findOneById($id);
            
            if($exercice !== null)
            {
                $formBuilder = $this->createFormBuilder($reverser);
                $formBuilder
                    ->add("question1", "entity", array(
                        'class'    => 'StockHG3appBundle:Question',
                        'choice_label' => 'corpsForm',
                        'multiple' => false,
                        "expanded" => true,
                        "query_builder" => function(\Doctrine\ORM\EntityRepository $er) use($exercice){
                            return $er->createQueryBuilder('q')->leftJoin("q.exercice", "exercice")->where('exercice.id = :id')->setParameter('id', $exercice->getId())->orderBy('q.numeroOrdre');
                        }
                    ))
                    ->add("question2", "entity", array(
                        'class'    => 'StockHG3appBundle:Question',
                        'choice_label' => 'corpsForm',
                        'multiple' => false,
                        "expanded" => true,
                        "query_builder" => function(\Doctrine\ORM\EntityRepository $er) use($exercice){
                            return $er->createQueryBuilder('q')->leftJoin("q.exercice", "exercice")->where('exercice.id = :id')->setParameter('id', $exercice->getId())->orderBy('q.numeroOrdre');
                        }
                    ))
                    ->add("envoyer", "submit")
                ;
                
                $form = $formBuilder->getForm();
                $form->handleRequest($request);
                
                if($form->isValid())
                {
                    $questionService->publierExercice($id);

                    $numeroQuestion1 = $reverser->getQuestion1()->getNumeroOrdre();
                    $numeroQuestion2 = $reverser->getQuestion2()->getNumeroOrdre();
                    
                    $question1 = $reverser->getQuestion1();
                    $question2 = $reverser->getQuestion2();
                    
                    $question2->setNumeroOrdre($numeroQuestion1);
                    $question1->setNumeroOrdre($numeroQuestion2);
                    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($question1);
                    $em->persist($question2);
                    $em->flush();
                    
                    $session->remove("questionActuel");
                    
                    return $this->redirectToRoute("question-liste", array("id" => $id));
                }
                
                return $this->render("@App/Admin/questionIntervertirAdmin.html.twig", array(
                    "form" => $form->createView(),
                    "exercice" => $exercice,
                ));
            }
            
            else{
                $flash->add("erreur", "L'exercice n'a pas été trouvé.");
                $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/conversion-generale", name="conversion-generale")
     */
    public function conversionGeneraleAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        $session = $request->getSession();
        
        $repositoryExercice = $this->getDoctrine()->getManager()->getRepository("AppBundle:Exercise");
        $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository("AppBundle:AssociationGroup");
        $repositoryTypeQuestion = $this->getDoctrine()->getManager()->getRepository("AppBundle:TypeQuestion");
        
        if($utilisateurService->permissionAdmin())
        {
            //Initialisation des variables
            
            $tableauQuestions = array();
            $tableauSousQuestions = array();
            
            $groupes = $repositoryAssociationGroup->selectAssociationGroup();
            
            $em = $this->getDoctrine()->getManager();
            
            foreach($groupes as $groupeBoucle){
                $questionTemp = new Question();
                $questionTemp->setPoints($groupeBoucle->getPoints());
                $questionTemp->setAide($groupeBoucle->getAide());
                $questionTemp->setCorps($groupeBoucle->getDescription());
                $questionTemp->setTexteATrou($groupeBoucle->getTexteATrou());
                $questionTemp->setExercice($groupeBoucle->getExercice());
                $questionTemp->setGroupe(null);
                
                if($groupeBoucle->getFinTexte() !== null){
                    $finDeTexteTemp = new SubQuestion();
                    $finDeTexteTemp->setCorps($groupeBoucle->getFinTexte());
                    $finDeTexteTemp->setTrouOuListe("F");
                    $finDeTexteTemp->setQuestion($questionTemp);
                }
                    
                $questions = $groupeBoucle->getQuestion();
                $i = 1;
                
                foreach($questions as $questionBoucle){
                    if($i === 1){
                        $questionTemp->setNumeroOrdre($questionBoucle->getNumeroOrdre());
                        
                        if($questionBoucle->getType()->getId() === 3){
                            $type = $repositoryTypeQuestion->findOneById(7);
                            $questionTemp->setType($type);
                        }
                        
                        else if($questionBoucle->getType()->getId() === 4){
                            $type = $repositoryTypeQuestion->findOneById(8);
                            $questionTemp->setType($type);
                        }
                        
                        else if($questionBoucle->getType()->getId() === 5){
                            $type = $repositoryTypeQuestion->findOneById(9);
                            $questionTemp->setType($type);
                        }
                    }
                    
                    $sousQuestionTemp = new SubQuestion();
                    $sousQuestionTemp->setCorps($questionBoucle->getCorps());
                    $sousQuestionTemp->setTrouOuListe($questionBoucle->getTrouOuliste());
                    $sousQuestionTemp->setQuestion($questionTemp);
                    
                    $bonnesReponses = $questionBoucle->getBonneReponses();
                    
                    foreach($bonnesReponses as $bonneReponseBoucle){
                        if($questionBoucle->getType()->getId() === 5){
//                            var_dump($bonneReponseBoucle->getCorps());
                        }
                        $bonneReponseBoucle->setSousQuestion($sousQuestionTemp);
                        $bonneReponseBoucle->setQuestion(null);
                        $sousQuestionTemp->addBonneReponse($bonneReponseBoucle);
                    }
                        
                    array_push($tableauSousQuestions, $sousQuestionTemp);
                    
                    $em->remove($questionBoucle);
                    $em->persist($sousQuestionTemp);
                    
                    $i++;
                }
                
                $ressources = $groupeBoucle->getRessources();
                
                foreach($ressources as $ressourceBoucle){
                        $ressourceBoucle->addQuestion($questionTemp);
                        $questionTemp->addRessource($ressourceBoucle);
                        $ressourceBoucle->removeGroupe($groupeBoucle);
                        $groupeBoucle->removeRessource($ressourceBoucle);
                        $em->persist($ressourceBoucle);
                    }
                
                if($questionTemp->getNumeroOrdre() === null){
                    $questionTemp->setNumeroOrdre(100 + random_int (0, 5000));
                }
                
                array_push($tableauQuestions, $questionTemp);
                        
                if($questionTemp->getType() === null){
                    $type = $repositoryTypeQuestion->findOneById(7);
                    $questionTemp->setType($type);
                }
                
                $em->remove($groupeBoucle);
                $em->persist($questionTemp);
            }
//            die;
            $em->flush();
            
            $exercices = $repositoryExercice->findAll();
            
            foreach($exercices as $exerciceBoucle){
                $questionService->verifNumeroOrdre($exerciceBoucle->getId());
            }
            
            return $this->render("@App/Admin/conversionGenerale.html.twig");
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}