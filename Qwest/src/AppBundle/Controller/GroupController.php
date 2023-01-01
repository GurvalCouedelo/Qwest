<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Question;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Form\AssociationGroupType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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

class GroupController extends Controller
{
    /**
     * @Route("/admin/question/modification-groupe/{id}", name="groupe-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function groupeModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $flash = $request->getSession();
            
            $groupe = $repositoryAssociationGroup->findOneById($id);
            
            if($groupe !== null)
            {
                $formBuilder = $this->createFormBuilder($groupe);
                $formBuilder
                    ->add('description', CKEditorType::class)
                    ->add('points', IntegerType::class, array(
                        "required" => false
                    ))
                    ->add('aide', TextType::class, array(
                        "required" => false
                    ))
                    ->add("envoyer", SubmitType::class)
                ;
                    
                $form = $formBuilder->getForm();
                $form->handleRequest($request);
                
                if($form->isValid()){
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($groupe);
                    $em->flush();
                    
                    return $this->redirect($this->generateUrl('question-liste', 
                        array("id" => $groupe->getExercice()->getId())) . '#' . $questionService->getLastQuestionGroup($groupe)->getNumeroOrdre()
                    );
                }
                
                return $this->render("@App/Admin/groupeModificationAdmin.html.twig", array(
                    "form" => $form->createView(),
                    "id" => $id,
                    "groupe" => $groupe
                ));
                
            }
            
            else{
                $flash->getFlashBag()->add("erreur", "L'exercice est introuvable.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/groupe/suppression/{id}/{true}", name="groupe-suppression", requirements={
     *         "id": "\d*",
     *         "true": "true|false"
     *     })
     */
    public function groupeSuppressionAction(Request $request, $id, $true)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $em = $this->getDoctrine()->getManager();

            $groupe = $repositoryAssociationGroup->findOneById($id);
            
            if($groupe === null)
            {
                $flash->getFlashBag()->add("erreur", "Le groupe n'est pas trouvé, il a du être supprimé.");
                    
                return $questionService->questionListeRedirection();
            }
            
            if($true === "true")
            {
                $exercice = $groupe->getExercice()->getId();
                $questionListe = $groupe->getQuestion();
                
                foreach($questionListe as $questionBoucle)
                {
                    if($questionBoucle->getType()->getNom() !== "Association")
                    {
                        $questionBoucle->setGroupe(null);
                        $em->persist($questionBoucle);
                    }
                }
                    
                $em->remove($groupe);
                $em->flush();
                
                $questionService->verifNumeroOrdre($exercice);
                
                return $questionService->questionListeRedirection();
            }
            
            else
            {
                return $this->render("@App/Admin/groupeSuppressionAdmin.html.twig", array(
                    "groupe" => $groupe
                ));
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}