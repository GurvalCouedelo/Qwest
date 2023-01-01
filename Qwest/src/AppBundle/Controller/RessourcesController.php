<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Exercise;
use AppBundle\Entity\Question;
use AppBundle\Entity\User;
use AppBundle\Entity\Means;
use AppBundle\Entity\Subject;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\Note;
use AppBundle\Form\MeansType;
use AppBundle\Form\QuestionAssociationType;
use AppBundle\Form\GoodAnswerQuizzType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use AppBundle\Service\BilletService;
use AppBundle\Service\UtilisateurService;
use AppBundle\Service\CalculResultatsService;
use AppBundle\Service\CaptchaService;
use AppBundle\Service\QuestionService;
use AppBundle\Service\RessourceService;
use AppBundle\Service\TexteATrouService;

class RessourcesController extends Controller
{
    /**
     * @Route("/admin/galerie/{type}/{matiere}", name="galerie", requirements={
     *         "type": "I|S|V",
               "matiere": "\d*"
     *     })
     */
    public function galerieAction(Request $request, $type, $matiere)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $matiereRepository = $this->getDoctrine()->getManager()->getRepository(Subject::class);
            $ressourceRepository = $this->getDoctrine()->getManager()->getRepository(Means::class);
            
            $matiereEntity = $matiereRepository->findOneById($matiere);
            
            if($matiereEntity !== null){
                $ressources = $ressourceRepository->findBy(
                    array(
                        "type" => $type,
                        "matiere" => $matiereEntity
                    ),
                    array("id" => "DESC")
                );
                
                return $this->render('@App/Admin/galerieAdmin.html.twig', array(
                    "ressources" => $ressources,
                    "type" => $type,
                    "matiere" => $matiereEntity,
                    "matiereListe" => $matiereRepository->findAll(),
                    "ressourceService" => $ressourceService,
                ));
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/question/envoyer-ressource/", name="ressource-creation")
     */
    public function ressourceCreationAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $flash = $request->getSession();
            $ressource = new Means();
            
            $formBuilder = $this->createFormBuilder($ressource);
            $formBuilder
                ->add("file", FileType::class, array(
                    "required" => false
                ))
                ->add("lien", TextType::class, array(
                    "required" => false
                ))
                ->add("alt", TextType::class, array(
                    "required" => false
                ))
                ->add('matiere', EntityType::class, array(
                    'class'    => Subject::class,
                    'choice_label' => 'nom',
                    'multiple' => false
                ))
                ->add("envoyer", SubmitType::class)
            ;
            
            $form = $formBuilder->getForm();
            $form->handleRequest($request);

            if($form->isValid()) {
                if($ressourceService->inspectionEnregistrementRessource($ressource) instanceof \AppBundle\Entity\Means)
                {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($ressource);
                    $em->flush();
                        
                    return $this->redirectToRoute('galerie', array(
                        "type" => $ressource->getType(), 
                        "matiere" => $ressource->getMatiere()->getId()
                    ));
                }
                    
                else{
                    $flash->getFlashBag()->add("erreur", $ressourceService->inspectionEnregistrementRessource($ressource));
                }
            }
            
            
                
            return $this->render('@App/Admin/ressourceCreationAdmin.html.twig', array(
                "form" => $form->createView(),
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("/admin/question/modification-ressource/{id}", name="ressource-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function ressourceModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $flash = $request->getSession();
            
            $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryMeans = $this->getDoctrine()->getManager()->getRepository(Means::class);
            
            $ressource = $repositoryMeans->findOneById($id);
            
            if($ressource !== null)
            {
                    
                $formBuilder = $this->createFormBuilder($ressource);
                $formBuilder
                    ->add("file", FileType::class, array(
                        "required" => false
                    ))
                    ->add("lien", TextType::class, array(
                        "required" => false
                    ))
                    ->add("alt", TextType::class, array(
                        "required" => false
                    ))
                    ->add('matiere', EntityType::class, array(
                        'class'    => Subject::class,
                        'choice_label' => 'nom',
                        'multiple' => false
                    ))
                    ->add("envoyer", SubmitType::class)
                ;
                $form = $formBuilder->getForm();
                $form->handleRequest($request);

                if ($form->isValid()) {
                    if($ressourceService->inspectionEnregistrementRessource($ressource, true) instanceof \AppBundle\Entity\Means)
                    {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($ressource);
                        $em->flush();
                        
                        return $this->redirectToRoute("galerie", array(
                            "type" => $ressource->getType(),
                            "matiere" => $ressource->getMatiere()->getId(),
                        ));
                    }
                    
                    else{
                        $flash->getFlashBag()->add("erreur", $ressourceService->inspectionEnregistrementRessource($ressource, true));
                    }
                }

                return $this->render('@App/Admin/ressourceModificationAdmin.html.twig', array(
                    "form" => $form->createView(),
                    "ressource" => $ressource
                ));
            }
            
            else
            {
                $flash->getFlashBag()->add("erreur", "La ressource est introuvable.");
                return $this->redirectToRoute("galerie", array(
                    "type" => "I",
                    "matiere" => 1
                ));
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/question/attribution-ressource-initialisation/{id}/{type}", name="ressource-attribution-initialisation", defaults={
     *        "type": "question"
     *     },
     *      requirements={
     *         "id": "\d*",
     *         "type": "question|groupe|reponse|billet|fond|exercice"
     *     })
     */
    public function ressourceAttributionInitialisationAction(Request $request, $id, $type)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            
            $session->set("entiteRessource", $id);
            $session->set("typeEntiteRessource", $type);
            
            return $this->redirectToRoute("galerie", array(
                "type" => "I",
                "matiere" => 1,
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/attribution-ressource-desinitialisation/{type}/{matiere}", name="ressource-attribution-desinitialisation", defaults={
     *         "type": "I" 
     *     },
     *     requirements={
     *         "type": "I|S|V",
     *         "matiere": "\d*"
     *     })
     */
    public function ressourceAttributionDesnitialisationAction(Request $request, $type, $matiere)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            
            $session->remove("entiteRessource");
            $session->remove("typeEntiteRessource");
            
            return $this->redirectToRoute("galerie", array(
                "type" => $type,
                "matiere" => $matiere,
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/question/{id}/attribution-ressource", name="ressource-attribution", requirements={
     *         "id": "\d*"
     *     })
     */
    public function ressourceAttributionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            $flash = $request->getSession();
            
            $repositoryMeans = $this->getDoctrine()->getManager()->getRepository(Means::class);
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryGoodAnswer = $this->getDoctrine()->getManager()->getRepository(GoodAnswer::class);
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $repositoryNote = $this->getDoctrine()->getManager()->getRepository(Note::class);
            $repositoryExercise = $this->getDoctrine()->getManager()->getRepository(Exercise::class);
            
            $ressource = $repositoryMeans->findOneById($id);
            
            if($ressource !== null){
                $type = $session->get("typeEntiteRessource");
                
                $idEntite = $session->get("entiteRessource");
                
                if($type === "groupe"){
                    $entite = $repositoryAssociationGroup->findOneById($idEntite);
                }
                elseif($type === "question" || $type === "fond"){
                    $entite = $repositoryQuestion->findOneById($idEntite);
                    
                }
                elseif($type === "reponse"){
                    $entite = $repositoryGoodAnswer->findOneById($idEntite);
                }
                elseif($type === "billet"){
                    $entite = $repositoryNote->findOneById($idEntite);
                }
                elseif($type === "exercice"){
                    $entite = $repositoryNote->findOneById($idEntite);
                }
                
                if($entite !== null){
                    $trouve = false;
                    
                    if($type === "groupe" || $type === "question")
                    {
                        foreach($entite->getRessources() as $ressourceBoucle){
                            if($ressource->getId() === $ressourceBoucle->getId()){
                                $trouve = true;
                                break;
                            }  
                        }
                    }
                    
                    elseif($type === "fond")
                    {
                        foreach($entite->getRessources() as $ressourceBoucle){
                            if($ressource->getId() === $ressourceBoucle->getId()){
                                $trouve = true;
                                break;
                            }  
                        }
                    }
                    
                    else{
                        if($ressource->getId() === $entite->getRessource()){
                            $trouve = true;
                        }  
                    }
                    
                    
                    if($trouve !== true){
                        $questionNumeroOrdre = 0;
                        
                        if($type === "groupe"){
                            $ressource->addGroupe($entite);
                            
                            $questionsGroupe = $repositoryQuestion->findBy(
                                array("groupe" => $entite->getId()),
                                array("numeroOrdre" => "ASC")
                            );
                            
                            foreach($entite->getQuestion() as $questionBoucle)
                            {
                                $questionNumeroOrdre = $questionBoucle->getNumeroOrdre();
                            }
                            
                            $exerciceId = $entite->getExercice()->getId();
                        }
                        
                        elseif($type === "question" || $type === "fond"){
                            $ressource->addQuestion($entite);
                            $questionNumeroOrdre = $entite->getNumeroOrdre();
                            $exerciceId = $entite->getExercice()->getId();
                        }

                        if($type === "groupe" || $type === "question")
                        {
                            $entite->addRessource($ressource);
                        }

                        else if($type === "reponse"){
                            $entite->setRessource($ressource);
                            $exerciceId = $entite->getQuestion()->getExercice()->getId();
                            $questionNumeroOrdre = $entite->getQuestion()->getNumeroOrdre();
                        }
                        
                        else if($type === "billet"){
                            $entite->setRessource($ressource);
                        } 
                        
                        else if($type === "fond"){
                            $entite->setRessourceFond($ressource);
                        } 
                        
                        else if($type === "exercice"){
                            $entite->setRessource($ressource);
                        } 

                        $em = $this->getDoctrine()->getManager();

                        $em->persist($entite);
                        $em->flush();

                        $session->remove("entiteRessource");
                        $session->remove("typeEntiteRessource");


                        if($type === "billet"){
                            throw new Exception($type);
                            return $this->redirectToRoute("accueil-admin");
                        }
                        elseif($type === "fond"){
                            return $this->redirectToRoute("carte-modification", array(
                                "id" => $entite->getId()
                            ));
                        }
                        elseif($type === "exercice"){
                            return $this->redirectToRoute("passerelle", array(
                                "page" => "exerciseur/" . $entite->getExercice()->getId() . "/0/true"
                            ));
                        }
                        
                        else{
                            return $this->redirectToRoute("passerelle", array(
                                "page" => "exerciseur/" . $entite->getExercice()->getId() . "/" . $entite->getId()
                            ));
                        }
                    }
                    
                    else{
                        $flash->getFlashBag()->add("message", "<p class=\"erreur\">Vous ne pouvez pas attribuer deux fois la même ressource à la même question.</p>");
                        
                        return $this->redirectToRoute("galerie", array(
                            "type" => $ressource->getType(),
                            "matiere" => $ressource->getMatiere()->getId(),
                        ));
                    }
                }
                
                else{
                    $flash->getFlashBag()->add("message", "<p class=\"erreur\">La question est introuvable.</p>");
                    return $this->redirectToRoute("galerie", array(
                        "type" => "I",
                        "matiere" => 1,
                    ));
                }
            }
            
            else{
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">La ressource est introuvable.</p>");
                return $this->redirtToRoute("galerie", array(
                    "type" => "I",
                    "matiere" => 1,
                ));
            }
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/question/{idQuestion}/{type}/desattribution-ressource/{idRessource}", name="ressource-desattribution", defaults={
     *        "type": "question" 
     *    },
     *    requirements={
     *      "idQuestion": "\d*",
     *       "idRessource": "\d*",
     *       "type": "question|groupe"
     *    })
     */
    public function ressourceDesattributionAction(Request $request, $idQuestion, $type, $idRessource)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryAssociationGroup = $this->getDoctrine()->getManager()->getRepository(AssociationGroup::class);
            $repositoryMeans = $this->getDoctrine()->getManager()->getRepository(Means::class);
            
            $flash = $request->getSession();
            
            $question = null;
            
            if($type === "groupe"){
                $entite = $repositoryAssociationGroup->findOneById($idQuestion);
                
                foreach($entite->getQuestion() as $questionBoucle)
                {
                    $question = $questionBoucle;
                    break;
                }
                
            }
            
            elseif($type === "question"){
                $entite = $repositoryQuestion->findOneById($idQuestion);
            }
            
            if($entite !== null){
                
                $ressource = $repositoryMeans->findOneById($idRessource);
                
                if($ressource !== null){
                    $trouve = false;

                    foreach($entite->getRessources() as $ressourceBoucle)
                    {
                        if($ressourceBoucle->getId() === $ressource->getId())
                        {
                            $trouve = true;
                        }
                    }

                    if($trouve === true){
                        if($type === "groupe"){
                            $ressource->removeGroupe($entite);
                        }
                        
                        elseif($type === "question"){
                            $ressource->removeQuestion($entite);
                        }
                        
                        $entite->removeRessource($ressource);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($entite);
                        $em->persist($ressource);
                        $em->flush();
                    }
                }

                if($question !== null){
                    return $this->redirect($this->generateUrl('question-liste', 
                        array("id" => $entite->getExercice()->getId())) . '#' . $question->getNumeroOrdre()
                    );
                }
                    
                else{
                    return $this->redirect($this->generateUrl('question-liste', 
                        array("id" => $entite->getExercice()->getId()))
                    );
                }
                
            }
            
            else{
                return $questionService->questionListeRedirection();
            }
            
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    
    
    /**
     * @Route("admin/ressource/suppression/{id}", name="ressource-suppression", requirements={
     *         "id": "\d*"
     *     })
     */
    public function ressourceSuppressionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repository = $this->getDoctrine()->getManager()->getRepository(Means::class);
            $em = $this->getDoctrine()->getManager();
            
            $ressource = $repository->findOneById($id);
            
            if($ressource === null)
            {
                $flash->getFlashBag()->add("erreur", "La ressource n'a pas été trouvée.");
            }
            
            else{
                $question = $ressource->getQuestion();
                $proposition = $ressource->getBonneReponse();
                $groupe = $ressource->getGroupe();
                
                if($question !== null)
                {
                    foreach($question as $questionBoucle){
                        $questionBoucle->removeRessource($ressource);
                    }
                }
                
                if($proposition !== null)
                {
                    foreach($proposition as $propositionBoucle){
                        $propositionBoucle->setRessource(null);
                    }
                }
                
                if($groupe !== null)
                {
                    foreach($groupe as $groupeBoucle){
                        $groupeBoucle->removeRessource($ressource);
                    }
                }

                $type = $ressource->getType();
                $matiere = $ressource->getMatiere()->getId();
                
                $em->remove($ressource);
                $em->flush();
            }
            
            return $this->redirectToRoute("galerie", array(
                "type" => $type,
                "matiere" => $matiere,
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/question/proposition-transformation/{id}/{type}", name="proposition-transformation", defaults={
     *         "type": "association"
     *     }, 
     *     requirements={
     *         "id": "\d*",
     *         "type": "association|quizz"
     *     })
     */
    public function propositionTransformationAction(Request $request, $id, $type)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        $flash = $request->getSession();
        
        if($utilisateurService->permissionAdmin())
        {
            $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
            $repositoryGoodAnswer = $this->getDoctrine()->getManager()->getRepository(GoodAnswer::class);
            $em = $this->getDoctrine()->getManager();
            
            $entite = ($type === "quizz") ? $repositoryGoodAnswer->findOneById($id) : $repositoryQuestion->findOneById($id);
            
            if($type === "quizz" && $entite instanceof \StockHG3\appBundle\Entity\GoodAnswer)
            {
                if($entite->getQuestion()->getType()->getNom() === "Quizz"){
                    $type = "quizz";
                    $exercice = $entite->getQuestion()->getExercice();
                }
                else{
                    $type = "incorrect";
                }
                
            }
            
            elseif($type === "association" && $entite instanceof \StockHG3\appBundle\Entity\Question){
                if($entite->getType()->getNom() === "Association"){
                    $type = "association";
                    $exercice = $entite->getExercice();
                }
                else{
                    $type = "incorrect";
                }
            }
            
            else{
                $type = null;
            }
                
                
            if($type === "quizz" || $type === "association")
            {
                $i = 0;
                
                if($type == "quizz"){
                    $ressourceEntite = $entite->getRessource();
                    
                    if($ressourceEntite === null)
                    {
                        $i = 0;
                    }
                    
                    else{
                        $i = 1;
                    }
                }
                
                elseif($type == "association")
                {
                    $ressourceEntite = $entite->getRessources();
                    
                    foreach($ressourceEntite as $ressource)
                    {
                        $i++;
                    }
                }
                    
                if($i !== 0)
                {
                    if($type === "quizz")
                    {
                        $form = $this->createForm(new GoodAnswerQuizzType(), $entite);
                    }
                    
                    elseif($type === "association")
                    {
                        $form = $this->createForm(new QuestionAssociationType(), $entite);
                    }
                    
                    $form->handleRequest($request);

                    if($form->isValid())
                    {
                        $questionService->publierExercice($exercice);

                        if($type === "quizz")
                        {
                            $entite->setRessource(null);
                            $em->remove($ressourceEntite);
                            
                            $em->persist($entite);
                            $em->flush();

                            return $this->redirect($this->generateUrl('question-liste', 
                                array("id" => $entite->getQuestion()->getExercice()->getId())) . '#' . $entite->getQuestion()->getNumeroOrdre()
                            );
                        }

                        elseif($type === "association")
                        {
                            foreach($entite->getRessources() as $ressource){
                                $entite->removeRessource($ressource);
                                $em->remove($ressource);
                                
                            }
                            
                            $em->persist($entite);
                            $em->flush();

                            return $this->redirect($this->generateUrl('question-liste', 
                                array("id" => $entite->getExercice()->getId())) . '#' . $entite->getNumeroOrdre()
                            );
                        }
                    }

                    if($type === "quizz")
                    {
                        return $this->render('@App/Admin/propositionQuizzTransformation.html.twig', array(
                            "form" => $form->createView(),
                            "entite" => $entite,
                            "type" => $type,
                        ));
                    }
                    
                    elseif($type === "association")
                    {
                        return $this->render('@App/Admin/propositionAssociationTransformation.html.twig', array(
                            "form" => $form->createView(),
                            "entite" => $entite,
                            "type" => $type,
                        ));
                    }
                    
                }
                    
                else{
                    $ressource = new Means();
                        
                    $form = $this->createForm(new MeansType(), $ressource);
                    $form->handleRequest($request);

                    if($form->isValid())
                    {
                        if($ressourceService->inspectionEnregistrementRessource($ressource) instanceof \StockHG3\appBundle\Entity\Means)
                        {
                            if($type === "quizz")
                            {
                                $ressource->addBonneReponse($entite);
                                $entite->setRessource($ressource);
                                $em->persist($entite);
                                $em->flush();

                                return $this->redirect($this->generateUrl('question-liste', 
                                    array("id" => $entite->getQuestion()->getExercice()->getId())) . '#' . $entite->getQuestion()->getNumeroOrdre()
                                );
                            }
                            
                            elseif($type === "association"){
                                $ressource->addQuestion($entite);
                                $entite->addRessource($ressource);
                                $em->persist($entite);
                                $em->flush();

                                return $this->redirect($this->generateUrl('question-liste', 
                                    array("id" => $entite->getExercice()->getId())) . '#' . $entite->getNumeroOrdre()
                                );
                            }
                            
                        }
                    
                        else{
                            $flash->getFlashBag()->add("erreur", $ressourceService->inspectionEnregistrementRessource($ressource));
                        }
                    }

                    return $this->render('@App/Admin/propositionRessourceTransformation.html.twig', array(
                        "form" => $form->createView(),
                        "entite" => $entite,
                        "type" => $type,
                    ));
                }
            }
                
            elseif($type === "incorrect")
            {
                $flash->getFlashBag()->add("erreur", "Cette page ne gère que les propositions de type \"Quizz\" et les premières propositions de type \"Association\".");
                    
                return $questionService->questionListeRedirection();
            }

            else{
                $flash->getFlashBag()->add("erreur", "La proposition n'a pas été trouvée.");
                return $questionService->questionListeRedirection();
            }
        }
        
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
    
    /**
     * @Route("admin/convertir", name="convertir")
     */
    public function convertirAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $questionService = $this->container->get(QuestionService::class);
        $ressourceService = $this->container->get(RessourceService::class);
        
        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            $flash = $request->getSession();
            
            $flash->getFlashBag()->add("erreur", $ressourceService->convertirImageEnRessource());
            
            return $this->redirectToRoute("galerie", array(
                "type" => "I",
                "matiere" => 5,
            ));
        }
        
        else{
            return $utilisateurService->redirection("A");
        }
    }
}