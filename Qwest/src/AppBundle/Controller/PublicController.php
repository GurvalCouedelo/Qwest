<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
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


class PublicController extends Controller
{
    /**
     * @Route("/", name="accueil")
     */
    public function indexAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);
        
        if($utilisateurService->verifConnection(false))
        {
            return $this->render('@App/Public/accueil.html.twig', array(
                "listNote" => $billetService->chercherBillet()
            ));
        }
      
        else{
            return $utilisateurService->redirection("NC");
        }
    }
    
    /**
     * @Route("a-propos", name="a-propos")
     */
    
    public function aProposPublicAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $billetService = $this->container->get(BilletService::class);

        if($utilisateurService->verifConnection(false))
        {
            return $this->render('@App/Public/aPropos.html.twig', array(
                "listNote" => $billetService->chercherAPropos()
            ));
        }
        
        else{
            return $utilisateurService->redirection("U");
        }
    }
    
    /**
     * @Route("/billet/{id}", name="billet", requirements={
     *         "id": "\d*"
     *     })
     */
    public function billetAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Note::class) 
        ;
        
        if($utilisateurService->verifConnection(false))
        {
            $note = $repository->findOneBy(
                array("id" => $id)
            );

            return $this->render('@App/Public/billet.html.twig', array(
                "note" => $note
            ));
        }
        
        else{
            return $utilisateurService->redirection("NC");
        }
    }
}