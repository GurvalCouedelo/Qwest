<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Question;
use AppBundle\Entity\Message;
use AppBundle\Entity\Points;
use AppBundle\Entity\User;
use AppBundle\Entity\TrainDiscussion;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Form\AssociationGroupType;
use AppBundle\Form\MessageType;
use AppBundle\Form\CorrectionReponseOuverteType;
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

class MessageController extends Controller
{

    /**
     * @Route("messagerie", name="messagerie")
     */
    public function messagerieAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);

        if($utilisateurService->permissionAdmin(true) || $utilisateurService->permissionUser())
        {
            $session = $request->getSession();
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryMessage = $this->getDoctrine()->getManager()->getRepository(Message::class);

            $utilisateur = $repositoryUser->findOneById($session->get("id"));
            $layout = $utilisateurService->getLayout();

            $messagesReçus = $repositoryMessage->findBy(array(
                "destinataire" => $utilisateur,
                "type" => "P"
            ));
            $messagesEnvoyes = $repositoryMessage->findBy(array(
                "envoyeur" => $utilisateur,
                "type" => "P"
            ));
            $reponsesOuverte = $repositoryMessage->findBy(
                array(
                    "type" => "R",
                    ),
                array(),
                20
                );

            return $this->render('@App/Gestion/messagerie.html.twig', array(
                "messagesRecus" => $messagesReçus,
                "messagesEnvoyes" => $messagesEnvoyes,
                "repositoryMessage" => $repositoryMessage,
                "reponsesOuverte" => $reponsesOuverte,
                "layout" => $layout,
            ));

        }

        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("discussion-privee/{id}", name="page-discussion-privee", requirements={
     *         "id": "\d*"
     *     })
     */
    public function pageDiscussionPriveeAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin(true) || $utilisateurService->permissionUser())
        {
            $session = $request->getSession();
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryMessage = $this->getDoctrine()->getManager()->getRepository(Message::class);
            $repositoryTrainDiscussion = $this->getDoctrine()->getManager()->getRepository(TrainDiscussion::class);

            $fil = $repositoryTrainDiscussion->findOneById($id);
            $utilisateur = $repositoryUser->findOneById($session->get("id"));

            if($fil !== null){
                $messages = $repositoryMessage->findBy(
                    array("fil" => $fil),
                    array("id" => "DESC")
                );
                $permission = false;

                foreach($messages as $messageBoucle){
                    if($messageBoucle->getEnvoyeur() === $utilisateur){
                        $permission = true;
                        break;
                    }

                    elseif($messageBoucle->getDestinataire() === $utilisateur){
                        $permission = true;
                        break;
                    }
                }

                if($permission === false){
                    $flash->getFlashBag()->add("message", "<p class=\"erreur\">Vous n'avez pas le droit d'accèder à cette discussion.</p>");

                    return $this->redirectToRoute("messagerie");
                }

                $layout = $utilisateurService->getLayout();

                $repositoryMessage->setLu($fil);
                $utilisateurService->verifCourier();

                return $this->render('@App/Gestion/pageDiscussionPrivee.html.twig', array(
                    "messages" => $messages,
                    "layout" => $layout,
                    "fil" => $fil
                ));
            }
            else{
                $flash->getFlashBag()->add("erreur", "Le fil de discussion n'a pas été trouvé.");

                return $this->redirectToRoute("messagerie");
            }
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("message-redaction/{id}", name="message-redaction", defaults={
     *     "id": null
     * },
     * requirements={
     *         "id": "\d*"
     *     })
     */
    public function messageRedactionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);
        $captchaService = $this->container->get(CaptchaService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin(true) || $utilisateurService->permissionUser())
        {
            $session = $request->getSession();
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryTrainDiscussion = $this->getDoctrine()->getManager()->getRepository(TrainDiscussion::class);
            $em = $this->getDoctrine()->getManager();

            $utilisateur = $repositoryUser->findOneById($session->get("id"));
            $layout = $utilisateurService->getLayout();

            $message = new Message();

            if($session->get("message") !== null)
            {
                $message->setObjet($session->get("message")["objet"]);
                $message->setContenu($session->get("message")["contenu"]);
            }

            if($id !== null){
                $fil = $repositoryTrainDiscussion->findOneById($id);

                if($fil !== null){
                    $reponse = 0;
                    $corrige = false;

//                    Verification du destinaire

                    foreach($fil->getMessage() as $messageBoucle){
                        if($messageBoucle->getType() === "R")
                        {
                            $reponse = $messageBoucle->getId();
                        }

                        if($messageBoucle->getType() === "C")
                        {
                            $corrige = true;
                        }

                        if($messageBoucle->getEnvoyeur() === $utilisateur){
                            $destinataire = $messageBoucle->getEnvoyeur();
                            $message->setDestinataire($destinataire);
                            $message->setType("M");
                            break;
                        }

                        elseif($messageBoucle->getDestinataire() === $utilisateur){
                            $destinataire = $messageBoucle->getDestinataire();
                            $message->setDestinataire($destinataire);
                            $message->setType("M");
                            break;
                        }
                    }

                    if($utilisateurService->permissionAdmin(true) && $reponse !== 0 && $corrige === false)
                    {
                        return $this->redirectToRoute("reponse-ouverte-correction", array(
                            "id" => $reponse
                        ));
                    }


                }

                else{
                    $flash->getFlashBag()->add("erreur", "Le fil de discussion n'a pas été trouvé.");
                    return $this->redirectToRoute("messagerie");
                }
            }

            else{
                $fil = new TrainDiscussion();
                $message->setType("P");

            }
            $message->setEnvoyeur($utilisateur);
            $message->setFil($fil);
            $message->setLu(false);

            $form = $this->createForm(MessageType::class, $message);
            $form->handleRequest($request);

            if($form->isValid()){
                if($session->get("permission") === "A"){
                    $message->setContenu($billetService->transformationTexte($message->getContenu()));
                    $message->setDateCreation(new \Datetime());

                    $em->persist($message);
                    $em->flush();
                    
                    return $this->redirectToRoute("page-discussion-privee", array(
                        "id" => $fil->getId()
                    ));
                }
                elseif($session->get("permission") === "U"){
                    $session->set("message", array("objet" =>  $message->getObjet(), "contenu" => $message->getContenu()));

                    if($captchaService->captchaverify($request->get('g-recaptcha-response')))
                    {
                        $message->setContenu($billetService->transformationTexte($message->getContenu()));
                        $message->setDateCreation(new \Datetime());

                        $em->persist($message);
                        $em->flush();

                        return $this->redirectToRoute("page-discussion-privee", array(
                            "id" => $fil->getId()
                        ));
                    }

                    else
                    {
                        $flash->getFlashBag()->add("message", "<p class=\"erreur\">Le captcha n'a pas permi de vous identifier en tant qu'humain.</p>");

                        if($fil->getId() === null)
                        {
                            $tableauRoute = array();
                        }else
                        {
                            $tableauRoute = array("fil" => $fil->getId());
                        }
                        return $this->redirectToRoute("message-redaction", $tableauRoute);
                    }
                }
            }

            return $this->render('@App/Gestion/messageRedaction.html.twig', array(
                "messages" => $message,
                "layout" => $layout,
                "form" => $form->createView(),
                "captcha" => $captchaService,
            ));
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("admin/reponse-ouverte-correction/{id}", name="reponse-ouverte-correction", requirements={
     *         "id": "\d*"
     *     })
     */
    public function reponseOuverteCorrectionAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin())
        {
            $session = $request->getSession();
            $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);
            $repositoryMessage = $this->getDoctrine()->getManager()->getRepository(Message::class);
            $em = $this->getDoctrine()->getManager();

            $utilisateur = $repositoryUser->findOneById($session->get("id"));

            $message = $repositoryMessage->findOneById($id);
            $correctionCree = null;

            if($message !== null)
            {
                foreach($message->getFil()->getMessage() as $messageBoucle)
                {
                    if($messageBoucle->getType() === "C")
                    {
                        $correctionCree = $messageBoucle;
                    }
                }

                if($correctionCree === null)
                {
                    $eleve = $message->getEnvoyeur();
                    $eleveTemp = new User();

                    $correction = new Message();
                    $correction->setObjet($message->getObjet(). " | correction");
                    $correction->setContenu($message->getContenu());
                    $correction->setDestinataire($eleveTemp);
                    $correction->setEnvoyeur($utilisateur);
                    $correction->setFil($message->getFil());
                    $correction->setLu(false);
                    $correction->setType("C");

                    $points = new Points();
                    $points->setExercice($message->getQuestion()->getExercice());
                    $points->setDateCreation(new \DateTime());
                    $points->setReponseOuverte($correction);
                    $correction->addPoint($points);

                    $eleveTemp->addReception($correction);
                    $eleveTemp->addResultat($points);
                }
                else{
                    $eleve = $message->getEnvoyeur();
                    $eleveTemp = new User();

                    $correction = new Message();
                    $correction->setObjet($correctionCree->getObjet());
                    $correction->setContenu($correctionCree->getContenu());
                    $correction->setDestinataire($eleveTemp);
                    $correction->setEnvoyeur($utilisateur);
                    $correction->setFil($correctionCree->getFil());
                    $correction->setLu(false);
                    $correction->setType("C");

                    $points = new Points();
                    $points->setExercice($message->getQuestion()->getExercice());
                    $points->setDateCreation(new \DateTime());
                    $points->setReponseOuverte($correction);

                    foreach($correctionCree->getPoints() as $pointBoucle)
                    {
                        $pointCree = $pointBoucle->getPoints();
                    }
                    $points->setPoints($pointCree);
                    $correction->addPoint($points);

                    $eleveTemp->addReception($correction);
                    $eleveTemp->addResultat($points);
                }


                $form = $this->createForm(CorrectionReponseOuverteType::class, $eleveTemp);
                $form->handleRequest($request);

                if($form->isValid()){
                    if($session->get("permission") === "A"){
                        $correction->setDateCreation(new \Datetime());
                        $correction->setDestinataire($eleve);

                        $eleve->addReception($correction);
                        $eleve->addResultat($points);
                        $points->setUtilisateur($eleve);

                        if($correctionCree !== null)
                        {
                            $message->setLu(true);
                            $em->persist($eleve);
                            $em->persist($message);
                            $em->remove($correctionCree);
                            $em->flush();
                        }

                        else{
                            $message->setLu(true);
                            $em->persist($eleve);
                            $em->persist($message);
                            $em->flush();
                        }



                        return $this->redirectToRoute("page-discussion-privee", array(
                            "id" => $correction->getFil()->getId()
                        ));
                    }
                }

                $layout = $utilisateurService->getLayout();

                return $this->render('@App/Admin/reponseOuverteCorrection.html.twig', array(
                    "messages" => $message,
                    "layout" => $layout,
                    "form" => $form->createView(),
                ));
            }

            else{
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">La réponse ouverte n'a pas pu être trouvée.</p>");

                return $this->redirectToRoute("messagerie");
            }
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
}