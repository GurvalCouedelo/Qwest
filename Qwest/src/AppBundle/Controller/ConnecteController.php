<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\User;
use AppBundle\Entity\Points;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Subject;

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

class ConnecteController extends Controller
{
    /**
     * @Route("/connecte", name="accueil-connecte", requirements={
     *         "id": "\d*"
     *     })
     */
    public function accueilConnecteAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);
        
        if($utilisateurService->permissionUser())
        {
            $session = $request->getSession();
            
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryPoints = $this->getDoctrine()->getManager()->getRepository(Points::class);
            $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);

            $utilisateur = $repositoryUser->findOneById($session->get("id"));
            $resultats = $repositoryPoints->findBy(
                array("utilisateur" => $utilisateur),
                array("dateCreation" => "DESC")
            );

            
            $positionNiveau = $repositoryUser->findUserPosition($utilisateur, true);
            $positionGenerale = $repositoryUser->findUserPosition($utilisateur);
            
            $exercicesSuggeres = $repositoryExercise->findExercicesPrioritaires($utilisateur);
            
            $visionnageAdmin = false;
            
            return $this->render('@App/Gestion/pageEleve.html.twig', array(
                "utilisateur" => $utilisateur,
                "resultats" => $resultats,
                "layout" => $utilisateurService->getLayout(),
                "positionNiveau" => $positionNiveau,
                "positionGenerale" => $positionGenerale,
                "exercicesSuggeres" => $exercicesSuggeres,
                "visionnageAdmin" => $visionnageAdmin,
                "totalPoint" => $repositoryPoints->countTotalPoints($utilisateur->getId()),
                "totalExercice" => $repositoryPoints->countTotalExercice($utilisateur->getId()),
                "pointsHonneur" => $repositoryPoints->countPointsHonneur($utilisateur->getId())
            ));
        }
      
        else{
            return $utilisateurService->redirection("U");
        }
    }
    
    /**
     * @Route("/connecte/a-propos", name="a-propos-connecte")
     */
    public function aProposConnecteAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);

        if($utilisateurService->permissionUser())
        {
            return $this->render('@App/Connecte/aProposConnecte.html.twig', array(
                "listNote" => $billetService->chercherAPropos()
            ));
        }
        
        else{
            return $utilisateurService->redirection("U");
        }
    }
    
    /**
     * @Route("/connecte/billet/{id}", name="billet-connecte", requirements={
     *         "id": "\d*"
     *     })
     */
    public function billetConnecteAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository("StockHG3appBundle:Note") 
        ;
        
        if($utilisateurService->permissionUser())
        {
            $note = $repository->findOneBy(
                array("id" => $id)
            );

            return $this->render('@App/Connecte/billetConnecte.html.twig', array(
                "note" => $note
            ));
        }
        
        else{
            return $utilisateurService->redirection("U");
        }
    }
    
    /**
     * @Route("/connecte/exercices-liste/{matiere}", name="exercice-liste-connecte", defaults={
     *         "matiere": ""
     *     })
     */
    public function exerciceListeConnecteAction(Request $request, $matiere)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        $repository = $this->getDoctrine()->getManager()->getRepository(Subject::class);
        $repositoryExercice = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
        $flash = $request->getSession();
        $session = $request->getSession();
        
        
        if($utilisateurService->permissionUser())
        {
            if(preg_match("#^[a-zA-Zéèàùëêïä\-]*$#", $matiere)){
                $matiereTransforme = preg_replace('#\-#', ' ', $matiere);
                $matiereEntite = $repository->findOneByNom(ucfirst($matiereTransforme));
                    
                $layout = $utilisateurService->getLayout();

                if($matiere === "tous-les-exercices" || $matiere === "")
                {
                    $exercises = $repositoryExercice->findExerciseOrderBy(true);
                }
                
                elseif($matiereEntite === null && $matiere !== "")
                {
                    $flash->getFlashBag()->add("erreur", "Cette matière n'existe pas.");
                    return $this->render("StockHG3appBundle:Exercice:exerciceListe.html.twig", array(
                        "exercices" => $exercises = $repositoryExercice->findByPublie(true),
                        "matiere" => $matiereEntite,
                        "layout" => $layout,
                    ));
                }

                else{
                    $exercises = $repositoryExercice->FEBSAC($matiereEntite->getId());
                }

                return $this->render("@App/Exercice/exerciceListe.html.twig", array(
                    "exercices" => $exercises,
                    "layout" => $layout,
                ));
            }
            
            else{
                $flash->getFlashBag()->add("erreur", "Le nom de cette matiere est incorrect.");
                return $this->redirectToRoute("accueil-connecte");
            }
        }
        
        else{
            return $utilisateurService->redirection("U");
        }
    }

    
    /**
     * @Route("/connecte/profil", name="profil-connecte")
     */
    public function profilConnecteAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $session = $request->getSession();
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);
        
        if($utilisateurService->permissionUser())
        {
            $user = $repository->findOneById($session->get("id"));

            $userTemp = new User();
            $userTemp->setPseudo($user->getPseudo());
            $userTemp->setNom($user->getNom());
            $userTemp->setPrenom($user->getPrenom());
            $userTemp->setClasseGroupe($user->getClasseGroupe());

            $form = $this->createForm(UserType::class, $userTemp);
            $form->handleRequest($request);

            if($form->isValid()){

                $user->setPseudo($userTemp->getPseudo());
                $user->setNom($userTemp->getNom());
                $user->setPrenom($userTemp->getPrenom());
                $user->setClasseGroupe($userTemp->getClasseGroupe());

                $em = $this->getDoctrine()->getManager();
                
                $em->persist($user);
                $em->flush();

                $session->set("pseudo", $user->getPseudo());
                return $this->redirectToRoute("accueil-connecte");
            }
            
            return $this->render('@App/Connecte/profilConnecte.html.twig', array(
                "form" => $form->createView(),
            ));
            
        }
        
        else{
            return $utilisateurService->redirection("U");
        }
    }
    
    /**
     * @Route("/connecte/changer-mot-de-passe", name="changer-mot-de-passe-connecte")
     */
    public function CMDPConnecteAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionUser())
        {
            $session = $request->getSession();
            $repository = $this->getDoctrine()->getManager()->getRepository(User::class);
            $flash = $request->getSession();
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
                return $this->redirectToRoute("accueil-connecte");
            }
            
            return $this->render('@App/Connecte/CMDPConnecte.html.twig', array(
                "form" => $form->createView(),
            ));
        }
        
        else{
            return $utilisateurService->redirection("U");
        }
    }
    
    /**
     * @Route("/connecte/classe-integrer/{id}", name="classe-integrer", requirements={
     *         "id": "\d*"
     *     })
     */
    public function classeIntegrerAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionUser())
        {
            $session = $request->getSession();
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryClassGroup = $this->getDoctrine()->getManager()->getRepository(ClassGroup::class);
            
            $classGroup = $repositoryClassGroup->findOneById($id);
            
            if($classGroup !== null){
                $utilisateur = $repositoryUser->findOneById($session->get("id"));

                if($classGroup->getNiveau()->getId() !== $utilisateur->getClasse()->getId()){
                    $flash->getFlashBag()->add("message", "<p class=\"erreur\">Cette classe n'est pas de votre niveau<p>");
                    return $this->redirectToRoute("liste-des-classes");
                }

                $utilisateur->setClasseGroupe($classGroup);


                
                $em = $this->getDoctrine()->getManager();
                $em->persist($utilisateur);
                $em->flush();
                
                $flash->getFlashBag()->add("message", "<p class=\"succes\">Vous avez intégré la classe " . $classGroup->getNom() . ".<p>");
                return $this->redirectToRoute("liste-des-classes");
            }
            
            else{
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">La classe n'a pas été trouvé.<p>");
                return $this->redirectToRoute("liste-des-classes");
            }
        }
        
        else{
            return $utilisateurService->redirection("U");
        }
    }

}