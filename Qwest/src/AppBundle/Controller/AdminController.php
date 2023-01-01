<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Note;
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

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Form\NoteType;
use AppBundle\Form\NoteNoImageType;
use AppBundle\Form\ImageSubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="accueil-admin")
     */
    public function accueilAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            return $this->render('@App/Admin/accueilAdmin.html.twig', array(
                "listNote" => $billetService->chercherBillet()
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("/admin/a-propos", name="a-propos-admin")
     */
    public function aProposAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            return $this->render("@App/Admin/aProposAdmin.html.twig", array(
                "listNote" => $billetService->chercherAPropos()
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("/admin/profil", name="profil-admin")
     */
    public function profilAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            $repository = $this->getDoctrine()->getManager()->getRepository(User::class);
            $user = $repository->findOneById($session->get("id"));
            
//            Formulaire
            
            $formBuilder = $this->createFormBuilder($user);
            $formBuilder 
                ->add('pseudo', TextType::class)
                ->add('nom', TextType::class)
                ->add('prenom', TextType::class)
                ->add('envoyer', SubmitType::class)
            ;
            
            $form = $formBuilder->getForm();
            
            if($form->handleRequest($request)->isValid()){
                $user->setClasse(null);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                
                return $this->redirectToRoute("accueil-admin");
            }
            
            return $this->render('@App/Admin/profilAdmin.html.twig', array(
                "form" => $form->createView(),
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("/admin/changer-mot-de-passe", name="changer-mot-de-passe-admin")
     */
    public function CMDPAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();
        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            $repository = $this->getDoctrine()->getManager()->getRepository(User::class);
            
//            Formulaire
            
            $user = $repository->findOneById($session->get("id"));
            $session->set("motDePasse", $user->getMotDePasse());
            $formBuilder = $this->createFormBuilder($user);
            
            $formBuilder
                ->add("motDePasse", PasswordType::class)
                ->add('motDePasseTemp', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'Le mot de passe n\'est pas le même dans les deux champs.',
                    'options' => array('attr' => array('class' => 'password-field')),
                    'required' => true,
                    'first_options'  => array('label' => "Votre mot de passe:"),
                    'second_options' => array('label' => "Répètez votre mot de passe:"),
                ))
                ->add("envoyer", SubmitType::class)
            ;
            
            $form = $formBuilder->getForm();
            
            if($form->handleRequest($request)->isValid()){
                if(hash("sha512", $user->getMotDePasse()) === $session->get("motDePasse"))
                {
                    $user->setMotDePasse(hash("sha512", $user->getMotDePasseTemp()));
                    $user->setMotDePasseTemp(null);
                        
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    
                    return $this->redirectToRoute("accueil-connecte");
                }
                
                $flash->getFlashBag()->add("erreur", "Votre mot de passe n'est pas le bon.");
                return $this->redirectToRoute("changer-mot-de-passe-admin");
            }
            
            return $this->render('@App/Admin/CMDPAdmin.html.twig', array(
                "form" => $form->createView(),
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}