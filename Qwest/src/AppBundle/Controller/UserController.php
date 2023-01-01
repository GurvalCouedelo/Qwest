<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Points;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\ClassGroup;
use AppBundle\Entity\Passerelle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Constraints\Length;
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


class UserController extends Controller
{
    /**
     * @Route("/connection", name="connection")
     */
    public function connectionAction(Request $request)
    {
        $flash = $request->getSession();
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $captchaService = $this->container->get(CaptchaService::class);

        if($utilisateurService->verifConnection(false))
        {
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $user = new User();
            $session = $request->getSession();

            $formBuilder = $this->createFormBuilder($user);
            $formBuilder
                ->add("pseudo", TextType::class)
                ->add('motDePasse', PasswordType::class)
                ->add('envoyer', SubmitType::class)
            ;

            $form = $formBuilder->getForm();
            $form->handleRequest($request);

            if($form->isValid()) {
                if($captchaService->captchaverify($request->get('g-recaptcha-response'))) {
                    $em = $this->getDoctrine()->getManager();
                    $utilisteurConnecte = $utilisateurService->connection($user->getPseudo(), $user->getMotDePasse());

                    //              Vérification du compte

                    if ($utilisteurConnecte === null) {
                        $flash->getFlashBag()->add("erreur", "Le compte est introuvable, vous n'avez pas dû envoyer le bon mot de passe.");
                        return $this->redirectToRoute("connection");
                    }

                    if ($utilisteurConnecte->getBloque() === "F") {
                        $session = $request->getSession();
                        $session->set("id", $utilisteurConnecte->getId());
                        $session->set("pseudo", $utilisteurConnecte->getPseudo());
                        $session->set("permission", $utilisteurConnecte->getPermission());
                        $session->set("nbCaptcha", 0);

                        $user = $repositoryUser->findOneById($utilisteurConnecte->getId());
                        $user->setDateConnection(new \DateTime());

                        $em->persist($user);
                        $em->flush();
                    } elseif ($utilisteurConnecte->getBloque() === "T") {
                        $flash->getFlashBag()->add("erreur", "Votre compte a été bloqué.");
                        return $this->redirectToRoute("connection");
                    }

                    //                Réorientation du visiteur
                    if ($utilisteurConnecte->getPermission() === "A") {
                        return $this->redirectToRoute('accueil-admin');
                    } else {
                        return $this->redirectToRoute('accueil-connecte');
                    }

                }

                else{
                    $flash->getFlashBag()->add("erreur", "Le captcha n'a pas permis de vous identifier en tant qu'humain.");
                    return $this->redirectToRoute("connection");
                }
            }

            return $this->render('@App/Public/connection.html.twig', array(
                "form" => $form->createView(),
                "captcha" => $captchaService,
            ));
        }

        else{
            return $this->redirectToRoute("accueil-admin");
        }
    }
    
    /**
     * @Route("/exercices", name="exercice")
     * @Route("/inscription", name="inscription")
     */
    
    public function inscriptionAction(Request $request)
    {
        $flash = $request->getSession();
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $captchaService = $this->container->get(CaptchaService::class);

        if($utilisateurService->verifConnection(false))
        {
            $user = new User();
            $formBuilder = $this->createFormBuilder($user);
            $formBuilder
                ->add("pseudo", TextType::class)
                ->add("nom", TextType::class)
                ->add("prenom", TextType::class)
                ->add("classeGroupe", EntityType::class, array(
                    "class" => ClassGroup::class,
                    "choice_label" => "nom",
                    "multiple" => false,
                    "group_by" => "niveau.nom"
                ))
                ->add('motDePasse', RepeatedType::class,
                    array(
                        'type' => PasswordType::class,
                        'invalid_message' => 'Le mot de passe n\'est pas le même dans les deux champs.',
                        'options' => array('attr' => array('class' => 'password-field')),
                        'required' => true,
                        'first_options'  => array('label' => "Votre mot de passe:"),
                        'second_options' => array('label' => "Répètez votre mot de passe:"),
                    )
                )
                ->add('envoyer', SubmitType::class)
            ;

            $user->setDateInscription(new \Datetime());
            $user->setDateConnection(new \Datetime());
            $user->setBloque("F");
            $user->setPermission("U");

            $form = $formBuilder->getForm();
            $form->handleRequest($request);

            if ($form->isValid() && $utilisateurService->estPasPersite($user->getPseudo())) {
                if($captchaService->captchaverify($request->get('g-recaptcha-response')))
                {
                    $session = $request->getSession();
                    $motDePasseDeBase = $user->getMotDePasse();
                    $user->hashMotDePasse();

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    $utilisteurConnecte = $utilisateurService->connection($user->getPseudo(), $motDePasseDeBase);

                    $session->set("id", $utilisteurConnecte->getId());
                    $session->set("pseudo", $utilisteurConnecte->getPseudo());
                    $session->set("permission", $utilisteurConnecte->getPermission());
                    $session->set("nbCaptcha", 0);

                    return $this->redirect($this->generateUrl('accueil-connecte'));
                }
                
                else
                {
                    $flash->getFlashBag()->add("erreur", "Le captcha n'a pas permis de vous identifier en tant qu'humain.");
                    return $this->redirectToRoute("inscription");
                }
                
            }
            
            elseif($form->isValid() && !$utilisateurService->estPasPersite($user->getPseudo())){
                $flash->getFlashBag()->add("erreur", "Ce compte existe déjà.");
                return $this->redirectToRoute("inscription");
            }

            return $this->render('@App/Public/inscription.html.twig', array(
                "form" => $form->createView(),
                "captcha" => $captchaService,
            ));
        }
        
        else{
            return $this->redirectToRoute("accueil-admin");
        }
    }
    

    
    /**
     * @Route("/deconnection", name="deconnection")
     */
    public function deconnectionAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->verifConnection(true))
        {
            $utilisateurService->deconnection();

            return $this->redirectToRoute("accueil");
        }
        
        else{
            return $this->redirectToRoute("accueil");
        }
    }
    
    /**
     * @Route("admin/blocage-utilisateur/{id}", name="blocage-utilisateur", requirements={
     *         "id": "\d*"
     *     })
     */
    public function blocageUtilisateurAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $flash = $request->getSession();
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $em = $this->getDoctrine()->getManager();
            
            $utilisateur = $repositoryUser->findOneById($id);
            
            if($utilisateur !== null){
                if($utilisateur->getBloque() === "F")
                {
                    $terme = "bloquage";
                }
                    
                else{
                    $terme = "débloquage";
                }
                
                $formBuilder = $this->createFormBuilder()
                    ->add('motDePasse', PasswordType::class)
                    ->add('envoyer', SubmitType::class)
                ;
            
                $form = $formBuilder->getForm();
                $form->handleRequest($request);

                if($form->isValid()){
                    if($utilisateurService->confirmationMDP($form->getData()["motDePasse"]))
                    {
                        if($utilisateur->getBloque() === "F")
                        {
                            $utilisateur->setBloque("T");
                        }

                        else{
                            $utilisateur->setBloque("F");
                        }

                        $em->persist($utilisateur);
                        $em->flush();

                        return $this->redirectToRoute("liste-des-eleves");
                    }
                    
                    else{
                        $flash->getFlashBag()->add("message", "<p class=\"erreur\">Le mot de passe est incorrect.</p>");
                    }
                    
                }
                
                return $this->render('@App/Gestion/blocageUtilisateur.html.twig', array(
                    "form" => $form->createView(),
                    "terme" => $terme
                ));
            }
            
            else{
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">L'utilisateur est introuvable.<\p>");
                
                return $this->redirectToRoute("liste-des-eleves");
            }
            
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("admin/eleve/{id}", name="page-eleve", requirements={
     *         "id": "\d*"
     *     })
     */
    public function pageEleveAdminAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);

        if($utilisateurService->permissionAdmin())
        {
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryPoints = $this->getDoctrine()->getManager()->getRepository(Points::class);
            $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);

            $utilisateur = $repositoryUser->findOneById($id);
            $resultats = $repositoryPoints->findBy(
                array("utilisateur" => $utilisateur),
                array("dateCreation" => "DESC")
            );
            
            if($utilisateur !== null){
                $positionNiveau = $repositoryUser->findUserPosition($utilisateur, true);
                $positionGenerale = $repositoryUser->findUserPosition($utilisateur);
                $exercicesSuggeres = $repositoryExercise->findExercicesPrioritaires($utilisateur);
                
                $visionnageAdmin = true;
                
                return $this->render("@App/Gestion/pageEleve.html.twig", array(
                    "utilisateur" => $utilisateur,
                    "resultats" => $resultats,
                    "layout" => $utilisateurService->getLayout(),
                    "positionNiveau" => $positionNiveau,
                    "positionGenerale" => $positionGenerale,
                    "visionnageAdmin" => $visionnageAdmin,
                    "exercicesSuggeres" => $exercicesSuggeres
                ));
            }

            else{
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">L'utilisateur est introuvable.<\p>");

                return $this->redirectToRoute("liste-des-eleves");
            }

        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("passerelle/{page}", name="passerelle", 
     *     requirements={
     *         "page": "[0-9a-zA-Z\?\/]*"
     *     })
     */
    public function passerelleAction(Request $request, $page)
    {
//        throw new Exception($page);
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        
        $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
        $repositoryPasserelle = $this->getDoctrine()->getManager()->getRepository(Passerelle::class);
        $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
        
        $session = $request->getSession();
        $pConnecte = $utilisateurService->permissionUser();
        $pAdmin = $utilisateurService->permissionAdmin(true);
        
        if($pConnecte || $pAdmin){
            $utilisateur = $repositoryUser->findOneById($session->get("id"));
            
            $passerelle = new Passerelle();
            $passerelle->setUtilisateur($utilisateur);
            
            $token = substr(bin2hex(random_bytes(32)), 0, 255);
            $passerelle->setToken($token);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($passerelle);
            $em->flush();
            
//            $epithete = "http://127.0.0.1/Qwest/Exercices/index.php/";
            $epithete = "https://exercices.titann.fr/index.php/";
            
            $page = preg_replace('#/#', '~', $page);
            
            return $this->redirect($epithete . "passerelle/index/" . strval($token) . "/" . urlencode($page));
            
        }

        else{
            return $this->redirectToRoute("accueil");
        }
    }
}