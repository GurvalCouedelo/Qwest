<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Question;
use AppBundle\Entity\TypeQuestion;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Entity\User;
use AppBundle\Entity\Means;
use AppBundle\Entity\Reverser;
use AppBundle\Form\GoodAnswerType;
use AppBundle\Form\ReverserType;
use AppBundle\Form\QuestionType;
use AppBundle\Form\QuestionAssociationType;
use AppBundle\Form\QuestionAssociationWithMeansType;
use AppBundle\Form\GoodAnswerAssociationType;
use AppBundle\Form\QuestionTrueOrFalseType;
use AppBundle\Form\PropositionUniqueType;
use AppBundle\Form\PropositionsModificationType;
use AppBundle\Form\VraiOuFauxModificationType;
use AppBundle\Form\GoodAnswerTrueOrFalseType;
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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use AppBundle\Service\BilletService;
use AppBundle\Service\UtilisateurService;
use AppBundle\Service\CalculResultatsService;
use AppBundle\Service\CaptchaService;
use AppBundle\Service\QuestionService;
use AppBundle\Service\RessourceService;
use AppBundle\Service\TexteATrouService;

class SubQuestionController extends Controller
{
    /**
     * @Route("admin/sous-question/creation/{groupe}/{typeQuest}", name="sous-question-creation", defaults={  
     *         "typeQuest": "Association" 
     *     },
     *     requirements={
     *         "groupe": "\d*",
     *         "typeQuest": "Association|Vrai ou faux"
     *     })
     */
    public function sousQuestionCreationAction(Request $request, $groupe, $typeQuest)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $billetService = $this->container->get(BilletService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryTypeQuestion = $this->getDoctrine()->getManager()->getRepository(TypeQuestion::class);
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $flash = $request->getSession();
            $session = $request->getSession();
            
            $groupe = $repositoryAssociationGroup->findOneById($groupe);
            
            if($groupe !== null){
                $exercice = $groupe->getExercice();
                
                if($exercice !== null)
                {
                    $exerciceId = $exercice->getId();
                    
                    $question = new Question();

                    foreach($groupe->getQuestion() as $questionBoucle)
                    {
                        $type = $questionBoucle->getType()->getNom();
                    }

                    if(isset($type))
                    {
                        if($type !== $typeQuest)
                        {
                            return $this->redirectToRoute("question-creation", array(
                                "id" => $exerciceId,
                            ));
                        }
                    }


    //                Création de sous-questions

                    if($typeQuest === "Association"){
                        $bonneReponse = new GoodAnswer();
                        $ressource = new Means();
                        $bonneReponse->setVerite(true);

                        $bonneReponse->setQuestion($question);
                        $question->addBonneReponse($bonneReponse);

                        $ressource->addQuestion($question);
                        $question->addRessource($ressource);

                        $question->setGroupe($groupe);
                        $question->setPoints(null);

                        $formBuilder = $this->createFormBuilder($question);
                        $formBuilder
                            ->add("corps", CKEditorType::class)
                            ->add('bonneReponses', CollectionType::class, array(
                                'entry_type'         => GoodAnswerAssociationType::class,
                                'allow_add'    => false,
                                'allow_delete' => false
                            ))
                            ->add("envoyer", SubmitType::class)
                        ;
                    }

                    elseif($typeQuest === "Vrai ou faux"){
                        $bonneReponse = new GoodAnswer();
                        $bonneReponse->setQuestion($question);
                        $question->addBonneReponse($bonneReponse);
                        $question->setGroupe($groupe);
                        $question->setPoints(null);
                        $formBuilder = $this->createFormBuilder($question);
                        $formBuilder
                            ->add("corps", CKEditorType::class)
                            ->add('bonneReponses', CollectionType::class, array(
                                'entry_type'         => GoodAnswerTrueOrFalseType::class,
                                'allow_add'    => false,
                                'allow_delete' => false
                            ))
                            ->add("envoyer", SubmitType::class)
                        ;
                    }


                    $form = $formBuilder->getForm();
                    $form->handleRequest($request);

                    switch($typeQuest){
                        case ("Association"):
                            $questionType = $repositoryTypeQuestion->findOneByNom("Association");
                        break;
                        case ("Vrai ou faux"):
                            $questionType = $repositoryTypeQuestion->findOneByNom("Vrai ou faux");
                        break;
                    }

                        $question->setType($questionType);


    //                Validation du formulaire


                    if($form->isValid()) {
                        $questionService->publierExercice($exerciceId);
                        $exercice = $repository->findOneById($exerciceId);


    //                    Création du numero de question


                        $questionGroupeByNumeroOrdre = $repositoryQuestion->findBy(
                            array("groupe" => $groupe), 
                            array("numeroOrdre" => "DESC"),
                            1
                        );

                        $numeroOrdreQuestion = 0;

                        foreach($questionGroupeByNumeroOrdre as $ques){
                            $numeroOrdreQuestion = $ques->getNumeroOrdre();
                        }

                        if($numeroOrdreQuestion === 0){
                            $questionRecherche = $repositoryQuestion->findByExercice($exercice->getId());

                            $i = 0;
                            foreach($questionRecherche as $questionTemp)
                            {
                                $i++;
                            }

                            $numeroOrdreQuestion = $i;
                        }


                        $numeroOrdre = $numeroOrdreQuestion + 1;
                        $repositoryQuestion->findQuestionBySuperiorNumeroOrdre($exerciceId, $numeroOrdre);


                        $questionService->verifNumeroOrdre($exerciceId);


                        $question->setCorps($billetService->transformationTexte($question->getCorps(), false));
                        $question->setExercice($exercice);
                        $question->setNumeroOrdre($numeroOrdre);


    //                    Suppression des images qui pourraient se créer malencontreusement

                        foreach($question->getRessources() as $ressource)
                        {
                            if($ressource->getFile() === null && $ressource->getLien() === null)
                            {
                                $ressource->removeQuestion($question);
                                $question->removeRessource($ressource);
                            }
                        }


    //                    Enregistrement

                        $em = $this->getDoctrine()->getManager();

                        if($question->getType() === null){
                            $questionType = $repositoryTypeQuestion->findOneByNom("Association");
                            $question->setType($questionType);
                        }

    //                    Enregistrement de propositions avec groupe désigné


                        if($question->getType()->getNom() === "Association" && $question->getGroupe() !== null)
                        {
                            $ressourceVerifFichier = $ressourceVerifLien = null; 


                            foreach($question->getRessources() as $ressourceBoucle){
                                $ressourceVerifFichier = $ressourceBoucle->getFile();
                                $ressourceVerifLien = $ressourceBoucle->getLien();
                            }

                            if($billetService->verifSiNull($question->getCorps()) && $ressourceVerifFichier === null && $ressourceVerifLien === null ){
                                $flash->getFlashBag()->add("message", "<p class=\"erreur\">Vous devez au moins complèter soit le champ de la consigne, soit le champ de la ressource.</p>"); 

                                return $this->redirectToRoute("question-creation", array(
                                    "id" => $exercice->getId(),
                                    "groupe" => $groupe->getId(),
                                    "typeQuest" => "Association"
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

                                            return $this->redirect($this->generateUrl('question-liste', 
                                                array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                                            );
                                        }

                                        else{
                                            $flash->getFlashBag()->add("message", "<p class=\"erreur\">" . $ressourceService->inspectionEnregistrementRessource($ressource) . "</p>");
                                            return $this->redirectToRoute("question-creation", array(
                                                "id" => $exercice->getId(),
                                                "groupe" => $groupe->getId(),
                                                "typeQuest" => "Association"
                                            ));
                                        }
                                    }

                                else{
                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($bonneReponse);
                                    $em->persist($question);
                                    $em->flush();

                                    return $this->redirect($this->generateUrl('question-liste', 
                                        array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                                    );
                                }
                            }
                        }

                        elseif($question->getType()->getNom() === "Vrai ou faux"  && $question->getGroupe() !== null)
                        {
                            $em->persist($bonneReponse);
                            $em->persist($question);
                            $em->flush();

                            return $this->redirect($this->generateUrl('question-liste', 
                                array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                            );
                        }
                    }


    //                Désignation des rendus


                    if($typeQuest === "Association"){
                        return $this->render('@App/Admin/questionAssociationCreationAdmin.html.twig', array(
                            "form" => $form->createView(),
                            "id" => $exerciceId,
                            "question" => $question
                        ));
                    }

                    if($typeQuest === "Vrai ou faux"){
                        return $this->render('@App/Admin/questionVraiOuFauxCreationAdmin.html.twig', array(
                            "form" => $form->createView(),
                            "id" => $exerciceId,
                            "question" => $question
                        ));
                    }
                }

                else
                {
                    $flash->getFlashBag()->add("erreur", "L'exercice est introuvable.");
                    return $questionService->questionListeRedirection();
                }
            }
                
            else{
                $flash->getFlashBag()->add("erreur", "La question est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    
    /**
     * @Route("/admin/sous-question/modification/{id}", name="sous-question-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function sousQuestionModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $billetService = $this->container->get(BilletService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $flash = $request->getSession();
            
            $propositionPremiere = $repository->findOneById($id);
            
            if($propositionPremiere !== null)
            {
                $exercice = $propositionPremiere->getExercice();
                
                if($propositionPremiere->getType()->getNom() === "Association" || $propositionPremiere->getType()->getNom() === "Vrai ou faux")
                {
                    $prositionSecondaire = $propositionPremiere->getBonneReponses();

                    $proposition1Temp = new Question();
                    $proposition1Temp->setCorps($propositionPremiere->getCorps());
                    $proposition1Temp->setType($propositionPremiere->getType());

                    foreach($propositionPremiere->getBonneReponses() as $bonneReponseBoucle){
                        $proposition1Temp->addBonneReponse($bonneReponseBoucle);
                    }

                    if($propositionPremiere->getType()->getNom() === "Association"){
                        $form = $this->createForm(PropositionsModificationType::class, $proposition1Temp);
                    }
                    
                    elseif($propositionPremiere->getType()->getNom() === "Vrai ou faux")
                    {
                        $form = $this->createForm(VraiOuFauxModificationType::class, $proposition1Temp);
                    }
                    
                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        foreach($proposition1Temp->getBonneReponses() as $bonneReponseBoucle)
                        {
                            $proposition1Temp->removeBonneReponse($bonneReponseBoucle);
                        }

                        $propositionPremiere->setCorps($proposition1Temp->getCorps());
                        $propositionPremiere->setType($proposition1Temp->getType());

                        unset($proposition1Temp);

                        $propositionPremiere->setCorps($billetService->transformationTexte($propositionPremiere->getCorps(), false));
                        
                        foreach($prositionSecondaire as $prositionBoucle){
                            if($prositionBoucle->getCorps() === null)
                            {
                                $prositionBoucle->setCorps("A complèter.");
                            }
                        }
                        $questionService->publierExercice($exercice->getId());

                        $em = $this->getDoctrine()->getManager();

                        $em->persist($propositionPremiere);
                        $em->flush();

                        return $this->redirect($this->generateUrl('question-liste', 
                            array("id" => $propositionPremiere->getExercice()->getId())) . '#' . $propositionPremiere->getNumeroOrdre()
                        );
                    }
                    
                    if($propositionPremiere->getType()->getNom() === "Association"){
                        return $this->render('@App/Admin/propositionsAssociationModificationAdmin.html.twig', array(
                            "form" => $form->createView(),
                            "proposition" => $propositionPremiere
                        ));
                    }
                    
                    elseif($propositionPremiere->getType()->getNom() === "Vrai ou faux"){
                        return $this->render('@App/Admin/propositionsVraiOuFauxModificationAdmin.html.twig', array(
                            "form" => $form->createView(),
                            "proposition" => $propositionPremiere
                        ));
                    }
                }
                
                else{
                    $flash->getFlashBag()->add("erreur", "Cette page ne gère que les propositions de type \"association\".");
                    return $questionService->questionListeRedirection();
                }
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "La proposition est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}