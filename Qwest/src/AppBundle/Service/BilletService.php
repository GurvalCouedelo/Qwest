<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\TypeNote;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;

class BilletService extends Controller
{
    public function chercherBillet()
    {
        $repositoryNote = $this->getDoctrine()->getManager()->getRepository(Note::class);
        $repositoryTypeNote = $this->getDoctrine()->getManager()->getRepository(TypeNote::class);
        
        $typeNote = $repositoryTypeNote->findOneByNom("accueil");
        $note = $repositoryNote->findByTypeNote($typeNote);
        
        $note = array_reverse($note);
        $note = array_slice($note, 0, 2);
        
        return $note;
    }
    
    public function chercherAPropos()
    {
        $repositoryNote = $this->getDoctrine()->getManager()->getRepository(Note::class);
        $repositoryTypeNote = $this->getDoctrine()->getManager()->getRepository(TypeNote::class);
        
        $typeNote = $repositoryTypeNote->findOneByNom("à propos");
        $note = $repositoryNote->findByTypeNote($typeNote);
        
        $note = array_reverse($note);
        $note = array_slice($note, 0, 7);
        
        return $note;
    }
    
    public function transformationTexte($texte, $baliseP = true)
    {
        $texte = html_entity_decode($texte);
        $texte = htmlspecialchars($texte);


        if($baliseP === true)
        {
            $tableauRemplacement = array(
                "&amp;\#39;" => "'",
                "&lt;p&gt;" => "",
                "&lt;/p&gt;" => "",
                "&lt;em&gt;" => "<em>",
                "&lt;/em&gt;" => "</em>",
                "&lt;strong&gt;" => "<strong>",
                "&lt;/strong&gt;" => "</strong>",
                "&lt;ul&gt;" => "<ul>",
                "&lt;/ul&gt;" => "</ul>",
                "&lt;li&gt;" => "<li>",
                "&lt;/li&gt;" => "</li>",
                "&lt;ol&gt;" => "<ol>",
                "&lt;/ol&gt;" => "</ol>",
                "&lt;u&gt;" => "<u>",
                "&lt;/u&gt;" => "</u>",
                "&lt;s&gt;" => "<s>",
                "&lt;/s&gt;" => "</s>",
                "&lt;br/&gt;" => "<br/>",
            );


        }
        else{
            $tableauRemplacement = array(
                "&amp;\#39;" => "'",
                "&lt;p&gt;" => "<p>",
                "&lt;/p&gt;" => "</p>",
                "&lt;em&gt;" => "<em>",
                "&lt;/em&gt;" => "</em>",
                "&lt;strong&gt;" => "<strong>",
                "&lt;/strong&gt;" => "</strong>",
                "&lt;ul&gt;" => "<ul>",
                "&lt;/ul&gt;" => "</ul>",
                "&lt;li&gt;" => "<li>",
                "&lt;/li&gt;" => "</li>",
                "&lt;ol&gt;" => "<ol>",
                "&lt;/ol&gt;" => "</ol>",
                "&lt;u&gt;" => "<u>",
                "&lt;/u&gt;" => "</u>",
                "&lt;s&gt;" => "<s>",
                "&lt;/s&gt;" => "</s>",
            );
        }


        foreach($tableauRemplacement as $tableauClef => $tableauContenu){
            $texte = preg_replace("#" . $tableauClef . "#", $tableauContenu, $texte);
        }
        
        $texte = preg_replace("#&\#39;#", "e", $texte);

        return $texte;
    }
    
    public function verifSiNull($texte)
    {
        $texteTemp = preg_replace('#<p>&nbsp;</p>#', '', $texte);
        
        return $texteTemp === "";
    }
}