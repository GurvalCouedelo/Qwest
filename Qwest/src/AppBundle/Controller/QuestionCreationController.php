<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Question;
use AppBundle\Entity\TypeQuestion;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Form\QuestionType;
use AppBundle\Service\BilletService;
use AppBundle\Service\UtilisateurService;
use AppBundle\Service\QuestionService;
use AppBundle\Service\RessourceService;


class QuestionCreationController extends Controller
{
    /**
     * @Route("admin/question/creation/{id}", name="question-creation")
     */
    public function questionCreationAction(Request $request, $id)
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
            
            $exercice = $repository->findOneById($id);
            
            if($exercice !== null)
            {
                $question = new Question();
                
                $session->set("creation", true);
                $formBuilder = $this->createFormBuilder($question);
                $formBuilder
                    ->add("corps", CKEditorType::class)
                    ->add("aide", TextType::class, array(
                        "required" => false
                    ))
                    ->add("points", IntegerType::class)
                    ->add('type', EntityType::class, array(
                        'class'    => TypeQuestion::class,
                        'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('t')
                                ->andWhere('t.nom LIKE :terme1 OR t.nom LIKE :terme2 OR t.nom LIKE :terme3')
                                ->setParameter(':terme1', "Question à trou")
                                ->setParameter(':terme2', "Quizz")
                                ->setParameter(':terme3', "Ouverte")
                            ;
                        },
                        'choice_label' => 'nom',
                        'multiple' => false
                    ))
                    ->add("envoyer", SubmitType::class)
                ;
                
                
                $form = $formBuilder->getForm();
                $form->handleRequest($request);
                
                
//                Validation du formulaire
                
                
                if($form->isValid()) {
                    $questionService->publierExercice($id);
                    $exercice = $repository->findOneById($id);
                    
                    
//                    Création du numero de question
                    
                    
                    $questionRecherche = $repositoryQuestion->findBy(
                        array("exercice" => $exercice->getId()),
                        array("numeroOrdre" => "ASC")
                    );

                    $i = 0;
                    foreach($questionRecherche as $questionTemp)
                    {
                        $i++;
                    }

                    $IdDernQuest = 0;

                    foreach($questionRecherche as $questionTemp)
                    {
                        $IdDernQuest = $questionTemp->getNumeroOrdre();
                    }

                    $numeroOrdre = $IdDernQuest + 1;
                    
                    
                    $questionService->verifNumeroOrdre($id);

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
                    
                    
//                    Enregistrement de questions simples
                    
                    $em = $this->getDoctrine()->getManager();
                    
                    if($question->getType() === null){
                        $questionType = $repositoryTypeQuestion->findOneByNom("Association");
                        $question->setType($questionType);
                    }
                    
                    
                    if($question->getType()->getNom() === "Question à trou")
                    {
                        $em->persist($question);
                        $em->flush();

                        return $this->redirectToRoute("reponse-creation", array("id" => $question->getId()));
                    }
                    
                    elseif($question->getType()->getNom() === "Quizz")
                    {
                        $em->persist($question);
                        $em->flush();
                        
                        return $this->redirectToRoute("reponse-creation", array(
                            "id" => $question->getId()
                        ));
                    }
                    
                    elseif($question->getType()->getNom() === "Ouverte")
                    {
                        $em->persist($question);
                        $em->flush();

                        return $this->redirect($this->generateUrl('question-liste',
                                array("id" => $question->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                        );
                    }
                    
                    elseif($question->getType()->getNom() === "Carte à trou"){
                        $em->persist($question);
                        $em->flush();

                        return $this->redirectToRoute("ressource-attribution-initialisation", array("id" => $question->getId(), "type" => "fond"));
                    }
                    
                    
//                    Enregistrement de questions-groupe
                    
                    
                        if($question->getType()->getNom() === "Association")
                        {
                            $groupePersist = new AssociationGroup();
                            $groupePersist->setDescription($question->getCorps());
                            $groupePersist->setExercice($exercice);
                            $groupePersist->setAide($question->getAide());
                            $groupePersist->setPoints($question->getPoints());

                            $em->persist($groupePersist);
                            $em->flush();

                            return $this->redirectToRoute("sous-question-creation", array(
                                "id" => $exercice->getId(),
                                "groupe" => $groupePersist->getId(),
                                "typeQuest" => "Association",
                            ));
                        }
                        
                        elseif($question->getType()->getNom() === "Vrai ou faux")
                        {
                            $groupePersist = new AssociationGroup();
                            $groupePersist->setDescription($question->getCorps());
                            $groupePersist->setExercice($exercice);
                            $groupePersist->setAide($question->getAide());
                            $groupePersist->setPoints($question->getPoints());

                            $em->persist($groupePersist);
                            $em->flush();

                            return $this->redirectToRoute("sous-question-creation", array(
                                "id" => $exercice->getId(),
                                "groupe" => $groupePersist->getId(),
                                "typeQuest" => "Vrai ou faux"
                            ));
                        }
                        
                        elseif($question->getType()->getNom() === "Texte à trou" && $question->getGroupe() === null)
                        {
                            if($question->getPoints() === 0 || $question->getPoints() === null)
                            {
                                $flash->getFlashBag()->add("message", "<p class=\"erreur\">Vous êtes obligé de mettre des points dans la question pour le type \"Texte à trou\".</p>");

                                return $this->redirectToRoute("question-creation", array(
                                    "id" => $exercice->getId(),
                                ));
                            }  

                            $groupePersist = new AssociationGroup();
                            $groupePersist->setDescription($question->getCorps());
                            $groupePersist->setExercice($exercice);
                            $groupePersist->setAide($question->getAide());
                            $groupePersist->setPoints($question->getPoints());

                            $em->persist($groupePersist);
                            $em->flush();

                            return $this->redirectToRoute("texte-a-trou-creation", array(
                                "id" => $groupePersist->getId(),
                            ));
                        }
                }
            
                return $this->render('@App/Admin/questionCreationAdmin.html.twig', array(
                    "form" => $form->createView(),
                    "id" => $id
                ));
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "L'exercice est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}