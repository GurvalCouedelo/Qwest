<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class UtilisateurService extends Controller
{
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    
    public function estPasPersite($pseudo)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        $user = $repository->findOneBy(
            array("pseudo" => $pseudo)
        );

        if($user === null)
        {
            return true;
        }
        
        else
        {
            return false;
        }
    }
    
    public function connection($pseudo, $motDePasse)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        $user = $repository->findOneBy(
            array("pseudo" => $pseudo, "motDePasse" => substr(hash("sha512", $motDePasse), 0, 124))
        );
        
        return $user;
    }
    
    public function deconnection()
    {
        $session = $this->container->get('session');
        
        $session->clear();
    }
    
    public function permissionAdmin($ignorer = false)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);
        $session = $this->container->get('session');

//        throw new Exception($session->get("id"));
        if(null !== $session->get("id"))
        {
            $user = $repository->findOneBy(
                array("id" => $session->get("id"))
            );

            if($user !== null)
            {
                if($user->getPermission() === "A")
                {
                    $this->verifCourier();
                    return true;
                }
                
                else{
                    if($ignorer === false)
                    {
                        throw $this->createNotFoundException('No route found for "GET /admin');
                    }

                    elseif($ignorer === true){
                        return false;
                    }
                }
            }
        }
    }

    public function permissionUser()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);
        $session = $this->container->get('session');

        if(null !== $session->get("id"))
        {
            $user = $repository->findOneBy(
                array("id" => $session->get("id"))
            );

            if($user->getPermission() === "U" && $user !== null)
            {
                $this->verifCourier();
                return true;
            }
        }

        return false;
    }

    public function verifCourier()
    {
        $repositoryMessage = $this->getDoctrine()->getManager()->getRepository(Message::class);
        $session = $this->container->get('session');

        $nombreMessage = intval($repositoryMessage->getNonlu($session->get("id")));

        if($nombreMessage === 0)
        {
            $texteMessage = "Messagerie";
        }

        elseif($nombreMessage === 1)
        {
            $texteMessage = "<span class=\"nouveau-message\">Un nouveau message</span>";
        }

        elseif($nombreMessage > 1)
        {

            $texteMessage = "<span class=\"nouveau-message\">" . strval($nombreMessage) . " nouveaux messages</span>";
        }

        $session->set("texteMessage", $texteMessage);

    }
    
    public function verifConnection($false)
    {
        
//        $session = $this->container->getSession();
        
        if($false === true)
        {
            if(null !== $this->session->get("id"))
            {
                return true;
            }

            else
            {
                return false;
            }
        }
        
        else
        {
            if(null === $this->session->get("id"))
            {
                return true;
            }

            else
            {
                return false;
            }
        }
    }
    
    public function redirection($permission = null)
    {
        $session = $this->container->get('session');

        if($session->get("permission") === null)
        {
            return $this->redirectToRoute("accueil");
        }
        if($session->get("permission") === "U")
        {
            return $this->redirectToRoute("accueil-connecte");
        }

        if($session->get("permission") === "A")
        {
            return $this->redirectToRoute("accueil-admin");
        }
    }
    
    public function confirmationMDP($motDePasse)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(User::class)
        ;
        
        
        $user = $repository->findOneByPermission("A");
        
        if(substr(hash("sha512", $motDePasse), 0, 124) === $user->getMotDePasse())
        {
            return true;
        }
        
        else{
            return false;
        }
    }
    
    public function getLayout()
    {
        return ($this->permissionAdmin(true)) ? "@App/adminLayout.html.twig" : "@App/connecteLayout.html.twig";
    }

    public function verifActualisationUtilisateur()
    {
        $repositoryUser = $this->getDoctrine()->getManager()->getRepository(User::class);

        $elevesMoins6Heures = $repositoryUser->findUserMoins6Heures();
        $i = 0;

        foreach($elevesMoins6Heures as $eleveBoucle){
            $i++;
        }

        if($i === 0){
            $nombreUtilisateurs = $repositoryUser->findNumberOfUser();
            $nombreUtilisateursNonActualise = $repositoryUser->findNumberOfUserNonActualise();
                
            if($nombreUtilisateurs === $nombreUtilisateursNonActualise){
                $repositoryUser->actualisationTotalPoints();
            }
        }
        
        if($i !== 0){
            $repositoryUser->actualisationTotalPoints();
        }
    }
}