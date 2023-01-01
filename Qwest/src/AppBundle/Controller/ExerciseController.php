<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\ExerciseType;
use AppBundle\Form\ExerciseNoIntroType;
use AppBundle\Form\QuestionType;
use AppBundle\Form\GoodAnswerType;
use AppBundle\Entity\Question;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\Means;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Classroom;
use AppBundle\Entity\TypeNote;
use AppBundle\Entity\Subject;
use AppBundle\Entity\Chapter;
use AppBundle\Entity\Passerelle;
use AppBundle\Entity\Difficulty;
use AppBundle\Entity\AssociationGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\File;
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

class ExerciseController extends Controller
{
    /**
     * @Route("admin/exercice-creation", name="exercice-creation")
     */
    public function exerciceCreationAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);

        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(TypeNote::class);
            $typeNote = $repository->findOneByNom("exercice");
            
            $exercise = new Exercise();
            $note = new Note();
            
            $exercise->setIntro($note);
            $note->setExercice($exercise);
            
            $form = $this->createForm(ExerciseType::class, $exercise);
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $note->setContenu($billetService->transformationTexte($note->getContenu(), false));
                $exercise->setDateCreation(new \Datetime());
                $exercise->setPublie(false);
                $note->setDateCreation(new \Datetime());
                $note->setDateModification(new \Datetime());
                $note->setTypeNote($typeNote);
                
                $em->persist($exercise);
                $em->flush();
                
//                return $this->redirectToRoute("question-creation", array("id" => $exercise->getId()));
                return $this->redirectToRoute("passerelle", 
                    array("page" => "exerciseur/" . $exercise->getId())
                );
            }

            return $this->render('@App/Admin/exerciceCreationAdmin.html.twig', array(
                "form" => $form->createView(),
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/exercice-modification/{id}", name="exercice-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function exerciceModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $repository = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $exercise = $repository->findOneById($id);

            if($exercise !== null)
            {
                $exerciceTemp = new Exercise();
                $exerciceTemp->setTitre($exercise->getTitre());
                $exerciceTemp->setChapitre($exercise->getChapitre());
                $exerciceTemp->setDifficulte($exercise->getDifficulte());

                $formBuilder = $this->createFormBuilder($exerciceTemp);
                $formBuilder
                    ->add('titre', TextType::class)
                    ->add('chapitre', EntityType::class, array(
                        'class'    => Chapter::class,
                        'choice_label' => 'nom',
                        'multiple' => false
                    ))
                    ->add('difficulte', EntityType::class, array(
                        'class'    => Difficulty::class,
                        'choice_label' => 'nom',
                        'multiple' => false
                    ))
                    ->add("envoyer", SubmitType::class)
                ;
                
                $form = $formBuilder->getForm();
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $exercise->setTitre($exerciceTemp->getTitre());
                    $exercise->setChapitre($exerciceTemp->getChapitre());
                    $exercise->setDifficulte($exerciceTemp->getDifficulte());

                    $em = $this->getDoctrine()->getManager();

                    $em->persist($exercise);
                    $em->flush();

                    return $this->redirectToRoute("question-liste", array("id" => $id));
                }

                return $this->render('@App/Admin/exerciceModification.html.twig', array(
                    "form" => $form->createView()
                ));
            }
            else{
                $flash->getFlashBag()->add("erreur", "L'exercice n'est pas trouvé, il a du être supprimé.");
                return $questionService->questionListeRedirection();
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/exercice/suppression/{id}/{true}", name="exercice-suppression", requirements={
     *         "id": "\d*",
     *         "true": "true|false"
     *     })
     */
    public function exerciceSuppressionAction(Request $request, $id, $true)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Exercise::class)
            ;

            $em = $this->getDoctrine()->getManager();

            $exercise = $repository->findOneById($id);
            
            if($exercise === null)
            {
                $flash->getFlashBag()->add("erreur", "L'exercice n'est pas trouvé, il a du être supprimé.");
                    
                return $this->redirectToRoute("exercice-liste-admin", array(
                    "id" => 0
                ));
            }
            
            if($true === "true")
            {
                $note = $exercise->getIntro();
                
                if($note !== null){
                    $note->setExercice(null);
                    $exercise->setIntro(null);
                    $em->remove($note);
                    $em->flush();
                }
                
                $em->remove($exercise);
                $em->flush();

                return $this->redirectToRoute("exercice-liste-admin", array(
                    "id" => 0
                ));
            }
            
            else
            {
                return $this->render("@App/Admin/exerciceSuppressionAdmin.html.twig", array(
                    "exercice" => $exercise
                ));
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/exercices-liste/{id}", name="exercice-liste-admin", requirements={
     *         "id": "\d*"
     *     })
     */
    public function exerciceListeAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        $repositoryClassroom = $this->getDoctrine()->getManager()->getRepository(Classroom::class);
        $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $flash = $request->getSession();
            $classe = $repositoryClassroom->findOneById($id);
            $layout = $utilisateurService->getLayout();
            
            
            if($classe === null && $id !== "" && $id !== "0")
            {
                $flash->getFlashBag()->add("erreur", "Cette matière n'existe pas.");
            }
            
            if($id === "0" || $id === "" || $id === "")
            {
                $exercises = $repositoryExercise->findExerciseOrderBy();
            }
            
            else{
                $exercises = $repositoryExercise->FEBC($id);
            }
            
            return $this->render("@App/Exercice/exerciceListe.html.twig", array(
                "exercices" => $exercises,
                "classe" => $classe,
                "layout" => $layout
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/questions-liste/{id}", name="question-liste", requirements={
     *         "id": "\d*"
     *     })
     */
    public function questionListeAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        $session = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            $repository2 = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            
            $exercise = $repository->findOneById($id);
            $questions = $repository2->findQuestionOrderByNumeroOrdre($id);
            
            if($exercise !== null)
            {
                $session->set("derniere-page", $request->get('_route'));
                $session->set("derniere-page-parametre", $id);
                
                return $this->render("@App/Admin/questionListeAdmin.html.twig", array(
                    "questions" => $questions,
                    "exercice" => $exercise,
                    "id" => $id
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
     * @Route("exercice/{id}/exercice-publier", name="exercice-publier", requirements={
     *         "id": "\d*"
     *     })
     */
    public function exercicePublierAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);

        if($utilisateurService->permissionAdmin())
        {
            return $questionService->publierExercice($id, false);
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("exercice/dupliquer/{id}/", name="exercice-dupliquer", requirements={
     *         "id": "\d*"
     *     })
     */
    public function dupliquerExerciceAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $flash = $request->getSession();
        $session = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $em = $this->getDoctrine()->getManager();
            $repositoryExercice = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            $exercice = $repositoryExercice->findOneById($id);
            
            if($exercice !== null)
            {
                $exerciceClone = clone $exercice;
                $exerciceClone->setId(null);
                
                foreach($exercice->getQuestion() as $questionBoucle){
                    $questionClone = clone $questionBoucle;
                    $questionClone->setId(null);
                    $questionClone->setExercice($exerciceClone);
                    $exerciceClone->addQuestion($questionClone);
                    $exerciceClone->setTitre($exercice->getTitre() . " x2");
                    $exerciceClone->setPublie(false);
                    
                    foreach($questionBoucle->getBonneReponses() as $bonneReponseBoucle){
                        $bonneReponseClone = clone $bonneReponseBoucle;
                        $bonneReponseClone->setId(null);
                        $bonneReponseClone->setQuestion($questionClone);
                        $questionClone->addBonneReponse($bonneReponseClone);
                    }
                    
                    foreach($questionBoucle->getSubQuestion() as $sousQuestionBoucle){
                        $sousQuestionClone = clone $sousQuestionBoucle;
                        $sousQuestionClone->setId(null);
                        $sousQuestionClone->setQuestion($questionClone);
                        $questionClone->addSubQuestion($sousQuestionClone);
                        
                        foreach($sousQuestionBoucle->getBonneReponse() as $bonneReponseBoucle){
                            $bonneReponseClone = clone $bonneReponseBoucle;
                            $bonneReponseClone->setId(null);
                            $bonneReponseClone->setSousQuestion($sousQuestionClone);
                            $sousQuestionClone->addBonneReponse($bonneReponseClone);
                        }
                    }
                    
                    foreach($questionBoucle->getRessources() as $ressourceBoucle){
                        $ressourceBoucle->addQuestion($questionClone);
                    }
                }
                
                $intro = $exercice->getIntro();
                $introClone = new Note;
                $introClone->setTitre($intro->getTitre() . " x2");
                $introClone->setContenu($intro->getContenu());
                $introClone->setTypeNote($intro->getTypeNote());
                $introClone->setDateCreation(new \Datetime());
                $introClone->setDateModification(new \Datetime());
                $introClone->setRessource($intro->getRessource());
                $introClone->setExercice($exerciceClone);
                
                
                $exerciceClone->setIntro($introClone);
                $em->persist($exerciceClone);
                $em->flush();
                
                return $this->redirectToRoute("question-liste", array("id" => $exerciceClone->getId()));
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
     * @Route("exercice/passerelle/{id}/", name="exercice-passerelle", requirements={
     *         "id": "\d*"
     *     })
     */
    public function exercicePasserelleAction(Request $request, $id)
    {
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
            
            $exercice = $repositoryExercise->findOneById($id);
            $passerelle->setExercice($exercice);
            
            $token = substr(bin2hex(random_bytes(32)), 0, 255);
            $passerelle->setToken($token);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($passerelle);
            $em->flush();
             
//            return $this->redirect('http://127.0.0.1/Qwest/Exercices/index.php/passerelle/index/' . strval($token). "/");
            return $this->redirect('https://exercices.titann.fr/index.php/passerelle/index/' . strval($token) . "/");
        }

        else{
            return $this->redirectToRoute("accueil");
        }
    }
}