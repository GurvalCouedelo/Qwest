<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\ClassGroup;
use AppBundle\Entity\User;
use AppBundle\Entity\Points;
use AppBundle\Entity\Classroom;
use AppBundle\Entity\Exercise;
use AppBundle\Form\ClassGroupType;

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

class BoardController extends Controller
{
    /**
     * @Route("admin/tableau-de-bord", name="tableau-bord-admin")
     */
    public function tableauDeBordAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $userRepository = $this->getDoctrine()->getManager()->getRepository(User::class);
            $pointsRepository = $this->getDoctrine()->getManager()->getRepository(Points::class);


            $nombreUtilisateur = $userRepository->findNumberOfUser();
            $nombreUtilisateur1hour = $nombreUtilisateur1day = $nombreUtilisateur1week = $pourcUtilisateur1hour = $pourcUtilisateur1day = $pourcUtilisateur1week = 0;

            if($nombreUtilisateur !== 0)
            {
                $nombreUtilisateur1hour = $userRepository->findNumberOfRecent1HourUser();
                $nombreUtilisateur1day = $userRepository->findNumberOfRecent1DayUser();
                $nombreUtilisateur1week = $userRepository->findNumberOfRecent1WeekUser();

                $nombreUtilisateur1week -= $nombreUtilisateur1day;
                $nombreUtilisateur1week = strval($nombreUtilisateur1week);

                $nombreUtilisateur1day -= $nombreUtilisateur1hour;
                $nombreUtilisateur1day = strval( $nombreUtilisateur1day);

                $pourcUtilisateur1hour = $nombreUtilisateur1hour / $nombreUtilisateur * 100;
                $pourcUtilisateur1day = $nombreUtilisateur1day / $nombreUtilisateur * 100;
                $pourcUtilisateur1week = $nombreUtilisateur1week / $nombreUtilisateur * 100;
                
                $tableauConnections = $userRepository->findListeConnections();
            }
            
            $nombreExercices1semaine = $pointsRepository->countExercices1semaine();
            $pointsHonneur1semaine = $pointsRepository->countPointsDhonneur1semaine();
                
            $tableauExercicesRealises = $pointsRepository->findListeExercicesRealises();
            
            $version = phpversion();
            
            $utilisateursRecents = $userRepository->findBy(
                array("permission" => "U"),
                array('dateConnection' => 'desc'),
                20,
                0
            );
            
            return $this->render('@App/Gestion/tableauDeBordAdmin.html.twig', array(
                "nombreUtilisateur" => $nombreUtilisateur,
                "nombreUtilisateur1hour" => $nombreUtilisateur1hour,
                "nombreUtilisateur1day" => $nombreUtilisateur1day,
                "nombreUtilisateur1week" => $nombreUtilisateur1week,
                
                "pourcUtilisateur1hour" => $pourcUtilisateur1hour,
                "pourcUtilisateur1day" => $pourcUtilisateur1day,
                "pourcUtilisateur1week" => $pourcUtilisateur1week,
                
                "nombreExercices1semaine" => $nombreExercices1semaine,
                "pointsHonneur1semaine" => $pointsHonneur1semaine,
                
                "tableauConnections" => $tableauConnections,
                "tableauExercicesRealises" => $tableauExercicesRealises,
                
                "version" => $version,
                "utilisateursRecents" => $utilisateursRecents,
                
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/liste-des-eleves", name="liste-des-eleves")
     */
    public function listeDesElevesAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $userRepository = $this->getDoctrine()->getManager()->getRepository(User::class);

            $utilisateurService->verifActualisationUtilisateur();

            $listeEleves = $userRepository->findUserForBoard();
            
            return $this->render('@App/Gestion/listeDesElevesAdmin.html.twig', array(
                "listeEleves" => $listeEleves,
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    

    /**
     * @Route("/tableau-d-honneur/{niveau}", name="tableau-honneur", defaults={
     *         "niveau": ""
     *     })
     */
    public function tableauHonneurAction(Request $request, $niveau)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);

        if($utilisateurService->permissionAdmin(true) || $utilisateurService->permissionUser())
        {
            $layout = $utilisateurService->getLayout();
            $flash = $request->getSession();
            $repositoryClassroom = $this->getDoctrine()->getManager()->getRepository(Classroom::class);
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);

            $utilisateurService->verifActualisationUtilisateur();
            
            if($niveau === null || $niveau === ""){
                $eleves = $repositoryUser->findUserForHonneurBoard();
            }
            
            else{
                $niveauTemp = $repositoryClassroom->findOneByNom($niveau);
                $eleves = $repositoryUser->findUserForHonneurBoard($niveauTemp);
            }

            return $this->render('@App/Gestion/tableauHonneur.html.twig', array(
                "eleves" => $eleves,
                "layout" => $layout,
                "niveau" => $niveau,
            ));
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/tableau-honneur-actualisation/{niveau}", name="tableau-honneur-actualisation", defaults={
     *         "niveau": ""
     *     })
     */
    public function tableauHonneurActualisationAction(Request $request, $niveau)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);

        if($utilisateurService->permissionAdmin())
        {
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryUser->actualisationTotalPoints();

            return $this->redirectToRoute("tableau-honneur", array(
                "niveau" => $niveau
            ));
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/infos-sur-exercices", name="infos-sur-exercices")
     */
    public function informationsSurExercicesAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);

        if($utilisateurService->permissionAdmin())
        {
            $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            $repositoryExercise->verifActualisationExercice();
            
            $listeExercices = $repositoryExercise->findExerciseOrderBy();
            $nombreExercices = $repositoryExercise->findNumberOfExercises();

            
            return $this->render('@App/Gestion/informationsSurExercices.html.twig', array(
                "listeExercices" => $listeExercices,
            ));
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    
    
    /**
     * @Route("admin/liste-des-classes", name="liste-des-classes")
     */
    public function listeDesClassesAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryClassGroup = $this->getDoctrine()->getManager()->getRepository(ClassGroup::class);
            $listeClasses = $repositoryClassGroup->FCBPOBC();
            
            $layout = $utilisateurService->getLayout();

            return $this->render('@App/Gestion/listeDesClassesAdmin.html.twig', array(
                "listeClasses" => $listeClasses,
                "layout" => $layout,
            ));

        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/classe-creation", name="classe-creation")
     */
    public function classeCreationAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);

            $proffesseur = $repositoryUser->findOneById($session->get("id"));
            
            $classe = new ClassGroup();
            
            $formBuilder = $this->createFormBuilder($classe);
            $formBuilder
                ->add("nom", TextType::class)
                ->add("niveau", EntityType::class, array(
                    'class'    => 'AppBundle:Classroom',
                    'choice_label' => 'nom',
                    'multiple' => false,
                    'expanded' => false,
                    'label' => false,
                ))
                ->add("envoyer", SubmitType::class)
            ;
            
            $form = $formBuilder->getForm();
            $form->handleRequest($request);
            
            if($form->isValid()){
                $em = $this->getDoctrine()->getManager();

                $classe->setProffesseur($proffesseur);
                $em->persist($classe);
                $em->flush();
                
                return $this->redirectToRoute("liste-des-classes");
            }

            $action = "C";

            return $this->render('@App/Gestion/classeCreationAdmin.html.twig', array(
                "form" => $form->createView(),
                "action" => $action,
                "classe" => $classe,
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("admin/classe-modification/{id}", name="classe-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function classeModificationAdminAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin())
        {
            $repositoryClassGroup = $this->getDoctrine()->getManager()->getRepository(ClassGroup::class);

            $classe = $repositoryClassGroup->findOneById($id);

            if($classe !== null){
                $formBuilder = $this->createFormBuilder($classe);
                $formBuilder
                    ->add("nom", TextType::class)
                    ->add("niveau", EntityType::class, array(
                            'class'    => Classroom::class,
                            'choice_label' => 'nom',
                            'multiple' => false
                        ))
                    ->add("envoyer", SubmitType::class)
                ;
            
            $form = $formBuilder->getForm();
            $form->handleRequest($request);

                if($form->isValid()){
                    $em = $this->getDoctrine()->getManager();

                    $em->persist($classe);
                    $em->flush();

                    return $this->redirectToRoute("liste-des-classes");
                }

                $action = "M";

                return $this->render('@App/Gestion/classeAdmin.html.twig', array(
                    "form" => $form->createView(),
                    "action" => $action,
                    "classe" => $classe
                ));
            }

            else{
                $flash->getFlashBag()->add("message", "<p class=\"succes\">La classe n\'a pas été trouvée, elle a du être supprimée.</p>");

                return $this->redirectToRoute("liste-des-classes");
            }
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("admin/classe/suppression/{id}/{true}", name="classe-suppression", requirements={
     *         "id": "\d*",
     *         "true": "true|false"
     *     })
     */
    public function classeSuppressionAdminAction(Request $request, $id, $true)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin())
        {
            $repositoryClassGroup = $this->getDoctrine()->getManager()->getRepository(ClassGroup::class);
            $em = $this->getDoctrine()->getManager();

            $classe = $repositoryClassGroup->findOneById($id);

            if($classe !== null){

                if($true === "true"){
                    $utilisateurs = $classe->getEleve();

                    foreach($utilisateurs as $utiBoucle){
                        $utiBoucle->setClasseGroupe(null);
                        $em->persist($utiBoucle);
                    }


                    $em->remove($classe);
                    $em->flush();

                    $flash->getFlashBag()->add("message", "<p class=\"succes\">La classe a été  supprimée.</p>");
                    return $this->redirectToRoute("liste-des-classes");
                }

                return $this->render('@App/Gestion/classeSuppressionAdmin.html.twig', array(
                    "classe" => $classe
                ));
            }

            else{
                $flash->getFlashBag()->add("erreur", "La classe n'a pas été trouvée, elle a du être supprimée.");

                return $this->redirectToRoute("liste-des-classes");
            }
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/exercices-prioritaires", name="exercices-prioritaires")
     */
    public function exercicesPrioritairesAdminAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin())
        {
            $classroomRepository = $this->getDoctrine()->getManager()->getRepository(Classroom::class);
            $classesPourExercicesPrioritaires = $classroomRepository->findClassesPourExercicesPrioritaires();
                
            $form = $this->createFormBuilder()
                ->add("exercice", EntityType::class, array(
                    'class'    => Exercise::class,
                    'choice_label' => 'titre',
                    'multiple' => false,
                    'expanded' => false,
                    'label' => false,
                    "group_by" => "chapitre.classe.nom"
                ))
                ->add("niveau", EntityType::class, array(
                    'class'    => Classroom::class,
                    'choice_label' => 'nom',
                    'multiple' => false,
                    'expanded' => false,
                    'label' => false,
                ))
                ->add("envoyer", SubmitType::class)
                ->getForm()
            ;

            $form->handleRequest($request);
            
            if($form->isValid()){
                $exercice = $form->get("exercice")->getData();
                $niveau = $form->get("niveau")->getData();
                
                foreach($exercice->getPrioritaire() as $niveauBoucle){
                    if($niveauBoucle->getId() === $niveau->getId()){
                        $flash->getFlashBag()->add("message", "<p class=\"erreur\">Cet exercice est déjà suggéré à ce niveau.</p>");
                        
                        return $this->redirectToRoute("exercices-prioritaires");
                    }
                }
                
                $exercice->addPrioritaire($niveau);
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($exercice);
                $em->flush();
                
                return $this->redirectToRoute("exercices-prioritaires");
            }
            
            return $this->render('@App/Gestion/exercicesPrioritairesAdmin.html.twig', array(
                "form" => $form->createView(),
                "classesPourExercicesPrioritaires" => $classesPourExercicesPrioritaires
            ));
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/desuggere-exercice/{exerciceId}/{niveauId}", name="desuggere-exercice", requirements={
     *         "exerciceId": "\d*",
     *         "niveauId": "\d*"
     *     })
     */
    public function dessugereExerciceAdminAction(Request $request, $exerciceId, $niveauId)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin())
        {
            $exerciseRepository = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            $classroomRepository = $this->getDoctrine()->getManager()->getRepository(Classroom::class);
                
            $exercice = $exerciseRepository->findOneById($exerciceId);
            
            if($exercice !== null){
                $niveau = $classroomRepository->findOneById($niveauId);
                
                if($niveau !== null){
                    $exercice->removePrioritaire($niveau);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($exercice);
                    $em->flush();
                }
                
                else{
                    $flash->getFlashBag()->add("message", "<p class=\"erreur\">Le niveau à qui déssuggérer l'exercice est introuvable.</p>");
                }
            }
            
            else{
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">L'exercice a déssuggérer est introuvable.</p>");
            }
            
            return $this->redirectToRoute("exercices-prioritaires");
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
}