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
use AppBundle\Form\QuestionTrueOrFalseType;
use AppBundle\Form\PropositionUniqueType;
use AppBundle\Form\PropositionsModificationType;
use AppBundle\Form\VraiOuFauxModificationType;
use AppBundle\Form\CarteType;
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

class SpecialEditorController extends Controller
{
    /**
     * @Route("admin/texte-a-trou/creation/{id}", name="texte-a-trou-creation", requirements={
     *         "id": "\d*"
     *     })
     */
    public function texteATrouCreationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $billetService = $this->container->get(BilletService::class);
        $texteATrouService = $this->container->get(TexteATrouService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $flash = $request->getSession();
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            
            $groupe = $repositoryAssociationGroup->findOneById($id);
            
            if($groupe !== null){
                $formBuilder = $this->createFormBuilder()
                    ->add('corps', CKEditorType::class)
                    ->add('envoyer', SubmitType::class)
                ;
            
                $form = $formBuilder->getForm();
                $form->handleRequest($request);
                
                if($form->isValid()){
                    $questionService->publierExercice($groupe->getExercice()->getId());
                    $confirmation = $texteATrouService->texteAEntite($billetService->transformationTexte($form->getData()["corps"], false), $groupe);
                    
                    if($confirmation === true)
                    {
                        $listeQuestions = $repositoryQuestion->findByGroupe($groupe);
                        
                        foreach($listeQuestions as $question){
                            $questionTemp = $question;
                            break;
                        }
                        
                        return $this->redirect($this->generateUrl('question-liste', 
                            array("id" => $groupe->getExercice()->getId())) . '#' . $questionTemp->getNumeroOrdre()
                        ); 
                    }

                    else{
                        $flash->getFlashBag()->add("erreur", $confirmation);
                        
                        return $this->redirect($this->generateUrl('texte-a-trou-creation', 
                            array("id" => $groupe->getId()
                        )));
                    }
                }
                
                return $this->render('@App/Admin/questionTexteATrouCreationAdmin.html.twig', array(
                    "form" => $form->createView(),
                    "id" => $groupe->getId(),
                ));
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "La groupe est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("/admin/texte-a-trou/modification/{id}", name="texte-a-trou-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function texteATrouModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $billetService = $this->container->get(BilletService::class);
        $texteATrouService = $this->container->get(TexteATrouService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $flash = $request->getSession();
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            
            $groupe = $repositoryAssociationGroup->findOneById($id);
            
            if($groupe !== null){
                $formBuilder = $this->createFormBuilder()
                    ->add('corps', CKEditorType::class, array(
                        'constraints' => array(
                           new NotBlank(),
                           new Length(array('max' => 15000)),
                        ),
                        "data" => $groupe->getTexteATrou(),
                    ))
                    ->add('envoyer', SubmitType::class)
                ;
                
                $form = $formBuilder->getForm();
                $form->handleRequest($request);
                
                if($form->isValid()){
                    $questionService->publierExercice($groupe->getExercice()->getId());
                    $confirmation = $texteATrouService->texteAEntite($billetService->transformationTexte($form->getData()["corps"], false), $groupe);
                    
                    if($confirmation === true)
                    {
                        $listeQuestions = $repositoryQuestion->findByGroupe($groupe);
                        
                        foreach($listeQuestions as $question){
                            $questionTemp = $question;
                            break;
                        }
                        
                        return $this->redirect($this->generateUrl('question-liste', 
                            array("id" => $groupe->getExercice()->getId())) . '#' . $questionTemp->getNumeroOrdre()
                        ); 
                    }

                    else{
                        $flash->getFlashBag()->add("erreur", $confirmation);
                        
                        return $this->redirect($this->generateUrl('texte-a-trou-creation', 
                            array("id" => $groupe->getId()
                        )));
                    }
                }
                
                return $this->render('@App/Admin/questionTexteATrouModificationAdmin.html.twig', array(
                    "form" => $form->createView(),
                    "id" => $groupe->getId(),
                ));
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "La groupe est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("/admin/carte/modification/{id}", name="carte-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function carteModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $billetService = $this->container->get(BilletService::class);
        $texteATrouService = $this->container->get(TexteATrouService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $flash = $request->getSession();
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            
            $question = $repositoryQuestion->findOneById($id);
            
            if($question !== null){
                $form = $this->createForm(CarteType::class, $question);
                $form->handleRequest($request);
                
                if($form->isValid()){
                    $question->setSubQuestion();
                    $questionService->publierExercice($question->getExercice()->getId());
                    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($question);
                    $em->flush();
                }
                
                return $this->render('@App/EditionExercice/carteModification.html.twig', array(
                    "form" => $form->createView(),
                    "question" => $question,
                ));
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "La groupe est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
}