<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Note;
use AppBundle\Entity\Chapter;
use AppBundle\Entity\Classroom;
use AppBundle\Entity\ClassGroup;
use AppBundle\Entity\Subject;
use AppBundle\Form\ChapterType;
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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Service\BilletService;
use AppBundle\Service\UtilisateurService;
use AppBundle\Service\CalculResultatsService;
use AppBundle\Service\CaptchaService;
use AppBundle\Service\QuestionService;
use AppBundle\Service\RessourceService;
use AppBundle\Service\TexteATrouService;

class ChapterController extends Controller
{
    /**
     * @Route("admin/creation-chapitre/", name="chapitre-creation")
     */
    public function chapitreCreationAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);

        if($utilisateurService->permissionAdmin())
        {

            $chapitre = new Chapter();

            $formBuilder = $this->createFormBuilder($chapitre);
            $formBuilder
                ->add("nom", TextType::class)
                ->add("matiere", EntityType::class, array(
                    'class'    => Subject::class,
                    'choice_label' => 'nom',
                    'multiple' => false,
                    'expanded' => false,
                    'label' => false
                ))
                ->add("classe", EntityType::class, array(
                    'class'    => Classroom::class,
                    'choice_label' => 'nom',
                    'multiple' => false,
                    'expanded' => false,
                    'label' => false
                ))
                ->add("envoyer", SubmitType::class)
            ;
            
            $form = $formBuilder->getForm();
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($chapitre);
                $em->flush();

                return $this->redirectToRoute("chapitres-liste");
            }

            return $this->render('@App/Admin/chapitreCreationAdmin.html.twig', array(
                "form" => $form->createView(),
            ));
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("/admin/chapitre/modification/{id}", name="chapitre-modification", requirements={
     *         "id": "\d*"
     *     })
     */
    public function chapitreModificationAction(Request $request, $id)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $repository = $this->getDoctrine()->getManager()->getRepository(Chapter::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin())
        {
            $chapitre = $repository->findOneById($id);

            if($chapitre !== null){
                $chapitreTemp = new Chapter();
                $chapitreTemp->setNom($chapitre->getNom());
                $chapitreTemp->setMatiere($chapitre->getMatiere());
                $chapitreTemp->setClasse($chapitre->getClasse());

                $formBuilder = $this->createFormBuilder($chapitre);
                $formBuilder
                    ->add("nom", TextType::class)
                    ->add("matiere", EntityType::class, array(
                        'class'    => Subject::class,
                        'choice_label' => 'nom',
                        'multiple' => false,
                        'expanded' => false,
                        'label' => false
                    ))
                    ->add("classe", EntityType::class, array(
                        'class'    => Classroom::class,
                        'choice_label' => 'nom',
                        'multiple' => false,
                        'expanded' => false,
                        'label' => false
                    ))
                    ->add("envoyer", SubmitType::class)
                ;

                $form = $formBuilder->getForm();
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $chapitre->setNom($chapitreTemp->getNom());
                    $chapitre->setMatiere($chapitreTemp->getMatiere());
                    $chapitre->setClasse($chapitreTemp->getClasse());

                    $em = $this->getDoctrine()->getManager();

                    $em->persist($chapitre);
                    $em->flush();

                    return $this->redirectToRoute("chapitres-liste");
                }

                return $this->render('@App/Admin/chapitreCreationAdmin.html.twig', array(
                    "form" => $form->createView(),
                    "chapitre" => $chapitre
                ));
            }

            else{
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">Le chapitre n'a pas été trouvé, il a du être supprimmé.</p>");
                return $this->redirectToRoute("chapitre-liste");
            }
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("admin/chapitre/suppression/{id}/{true}", name="chapitre-suppression", requirements={
     *         "id": "\d*",
     *         "true": "true|false"
     *     })
     */
    public function chapitreSuppressionAction(Request $request, $id, $true)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);
        $flash = $request->getSession();

        if($utilisateurService->permissionAdmin())
        {
            $em = $this->getDoctrine()->getManager();
            $repositoryChapter = $this->getDoctrine()->getManager()->getRepository(Chapter::class);

            $chapitre = $repositoryChapter->findOneById($id);

            if($chapitre === null)
            {
                $flash->getFlashBag()->add("message", "<p class=\"erreur\">Le chapitre n'a pas été trouvé, il a du être supprimmé.</p>");

                return $this->redirectToRoute("chapitres-liste");
            }

            if($true === "true")
            {
                $em->remove($chapitre);
                $em->flush();

                return $this->redirectToRoute("chapitres-liste");
            }

            else
            {
                return $this->render("@App/Admin/chapitreSuppressionAdmin.html.twig", array(
                    "chapitre" => $chapitre
                ));
            }
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }

    /**
     * @Route("admin/chapitres-liste", name="chapitres-liste")
     */
    public function chapitresListeAction(Request $request)
    {
        $utilisateurService = $this->container->get(UtilisateurService::class);

        if($utilisateurService->permissionAdmin())
        {
            $repositoryChapter = $this->getDoctrine()->getManager()->getRepository(Chapter::class);

            $chapitres = $repositoryChapter->findChapitresOrderByMatiere();

            return $this->render("@App/Admin/chapitresListeAdmin.html.twig", array(
                "chapitres" => $chapitres
            ));
        }

        else{
            return $utilisateurService->redirection("A");
        }
    }
}