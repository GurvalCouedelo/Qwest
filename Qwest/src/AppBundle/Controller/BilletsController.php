<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Note;
use AppBundle\Entity\TypeNote;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\User;
use AppBundle\Form\NoteType;
use AppBundle\Form\NoteNoImageType;
use AppBundle\Form\ImageSubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
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


class BilletsController extends Controller
{
    /**
     * @Route("admin/billet-creation/{type}/{id}", name="billet-creation", 
     *         defaults={
               "id": "",
               "type": "accueil"
               }, requirements={
     *         "id": "\d*",
     *         "type": "accueil|a-propos|exercice"
     *     })
     */
    public function billetCreationAction(Request $request, $id, $type)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);
        $questionService = $this->container->get(QuestionService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryTypeNote = $this->getDoctrine()->getManager()->getRepository(TypeNote::class);
            $repositoryExercice = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            
            $note = new Note();
            $form = $this->createForm(NoteType::class, $note);

            if($form->handleRequest($request)->isValid()) {
                
                if($type === "accueil")
                {
                    $typeNote = $repositoryTypeNote->findOneByNom("accueil");
                    $note->setTypeNote($typeNote);
                }
                
                elseif($type === "a-propos")
                {
                    $typeNote = $repositoryTypeNote->findOneByNom("à propos");
                    $note->setTypeNote($typeNote);
                }
                
                elseif($type === "exercice")
                {
                    if($id !== null)
                    {
                        $exercice = $repositoryExercice->findOneById($id);
                        
                        if($exercice !== null)
                        {
                            $typeNote = $repositoryTypeNote->findOneByNom("exercice");
                            $note->setTypeNote($typeNote); 
                            $note->setExercice($exercice); 
                            $exercice->setIntro($note); 
                        }
                        else{
                            return $questionService->questionListeRedirection();
                        }
                    }
                    
                    else{
                        return $questionService->questionListeRedirection();
                    }
                }
                
                $note->setDateCreation(new \Datetime());
                $note->setDateModification(new \Datetime());
                
                if($note->getImage() === null)
                {
                    $note->setImage(null);
                }
                
                $em = $this->getDoctrine()->getManager();
                
                if($type === "exercice")
                {
                    $em->persist($exercice);
                }
                $note->setContenu($billetService->transformationTexte($note->getContenu()));
                $em->persist($note);
                $em->flush();
                
                if($type === "accueil")
                {
                    return $this->redirectToRoute("ressource-attribution-initialisation", array(
                        "id" => $note->getId(),
                        "type" => "billet"
                    ));
                }
                
                elseif($type === "a-propos")
                {
                    return $this->redirectToRoute("a-propos-admin");
                }
                
                elseif($type === "exercice")
                {
                    return $questionService->questionListeRedirection();
                }
                
            }

            return $this->render('@App/Admin/billetCreationAdmin.html.twig', array(
                "form" => $form->createView(),
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/billet-modification/{id}", name="billet-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function billetModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $billetService = $this->container->get(BilletService::class);
        
            $repository = $this->getDoctrine()->getManager()->getRepository(Note::class);
            $em = $this->getDoctrine()->getManager();
            
            $note = $repository->findOneById($id);
            
            if($note !== null)
            {
                $formBuilder = $this->createFormBuilder($note);
                $formBuilder 
                    ->add('titre', TextType::class)
                    ->add('contenu', CKEditorType::class)
                    ->add('envoyer', SubmitType::class)
                ;
                $form = $formBuilder->getForm();
                
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $note->setDateModification(new \Datetime());
                    $note->setContenu($billetService->transformationTexte($note->getContenu()));
                    $em->persist($note);
                    $em->flush();

                    if($note->getTypeNote()->getNom() === "accueil")
                    {
                        return $this->redirectToRoute("accueil-admin");
                    }
                    
                    elseif($note->getTypeNote()->getNom() === "à propos")
                    {
                        return $this->redirectToRoute("a-propos-admin");
                    }
                    
                    elseif($note->getTypeNote()->getNom() === "exercice")
                    {
                        return $this->redirectToRoute("question-liste", array("id" => $note->getExercice()->getId()));
                    }
                    
                }

                return $this->render('@App/Admin/billetModificationAdmin.html.twig', array(
                    "note" => $note,
                    "form" => $form->createView()
                ));
            }
            
            else{
                $flash->getFlashBag()->add("erreur", "Le billet à modifier n'a pas été trouvée.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/banniere-suppression/{id}", name="banniere-suppression", requirements={
     *         "id": "\d*"
     *     })
     */
    public function banniereSuppressionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryNote = $this->getDoctrine()->getManager()->getRepository(Note::class);
            $billet = $repositoryNote->findOneById($id);
            
            if($billet !== null){
                $billet->setRessource(null);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($billet);
                $em->flush();
            }
            
            else{
                $flash->getFlashBag()->add("erreur", "La banniere à supprimer n'a pas été trouvée.");
            }
            
            return $this->redirectToRoute("accueil-admin");
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/billet-suppression/{id}", name="billet-suppression", requirements={
     *         "id": "\d*"
     *     })
     */
    public function billetSuppressionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $flash = $request->getSession();
            
            $repository = $this->getDoctrine()->getManager()->getRepository(Note::class);
            
            $note = $repository->findOneBy(
                array("id" => $id)
            );
            
            if ($note !== null) {
                $em = $this->getDoctrine()->getManager();
                
                if($note->getTypeNote()->getNom() === "exercice")
                {
                    $exercice = $note->getExercice();
                    $exercice->setIntro(null);
                    
                    $em->persist($exercice);
                    $em->remove($note);
                    $em->flush();
                    
                    return $this->redirectToRoute("question-liste", array("id" => $note->getExercice()->getId()));
                }
                
                elseif($note->getTypeNote()->getNom() === "à propos")
                {
                    $em->remove($note);
                    $em->flush();
                    
                    return $this->redirectToRoute("a-propos-admin");
                }
                
                else
                {
                    $em->remove($note);
                    $em->flush();
                    
                    return $this->redirectToRoute("accueil-admin");
                }
            }

            else
            {
                $flash->getFlashBag()->add("erreur", "Le billet est introuvable, vous l'avez probablement supprimmé.");
                return $this->redirectToRoute("accueil-admin");
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}