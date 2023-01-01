<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Entity\Means;
use AppBundle\Entity\Image;
use AppBundle\Entity\Subject;
use AppBundle\Entity\Note;

class RessourceService extends Controller
{
    private $acceptes = array("jpg", "jpeg", "png", "gif", "mpga", "ogx");
    private $images = array("jpg", "jpeg", "png", "gif");
    private $sons = array("mpga", "ogx");
    
    public function inspectionEnregistrementRessource(\AppBundle\Entity\Means $ressource, $modification = false)
    {  
        
        if($ressource->getLien() !== null)
        {
            if(preg_match("#^https://www\.youtube\.com/watch\?v=[A-Za-z0-9_]*#", $ressource->getLien()))
            {
                if($ressource->getAlt() !== null)
                {
                    $ressource->setLien(preg_replace('#watch\?v=#', 'embed/', $ressource->getLien()));
                    $ressource->setType("V");
                    return $ressource;
                }
                
                else{
                    return "Vous n'avez pas noté de description.";
                }
            }
                   
            else{
                return "L'adresse n'est pas valide.";
            }
        }
            
        elseif($ressource->getFile() !== null)
        {
            if(in_array($ressource->getFile()->guessExtension(), $this->acceptes))
            {
                if($ressource->getAlt() !== null)
                {
                    if(in_array($ressource->getFile()->guessExtension(), $this->images))
                    {
                        $ressource->setType("I");

                        return $ressource;
                    }

                    elseif(in_array($ressource->getFile()->guessExtension(), $this->sons))
                    {
                        $ressource->setType("S");

                        return $ressource;
                    }
                }
                
                else{
                    return "Vous n'avez pas noté de description.";
                }
            }
                
                
            else{
                return "Nous n'acceptons que les fichiers ayant ces extensions:" . $this->getAcceptes();
            }
        }
        
        elseif($modification === true && $ressource->getFile() == null && $ressource->getLien() == null){
            $repositoryMeans = $this->getDoctrine()->getManager()->getRepository(Means::class);
            $ressourceTemp = $repositoryMeans->findOneById($ressource->getId());
            
            if($ressourceTemp->getLien() == null){
                $ressource->setLien($ressourceTemp->getLien());
                return $ressource;
            }
            
            else{
                return "Vous ne pouvez pas retirer les liens.";
            }
        }
        
                   
        else{
            return "Vous n'avez rien envoyé.";
        }
    }
    
    public function getAcceptes()
    {
        return " ." . implode(" .", $this->acceptes) . " .";
    }
    
    public function createRessource($entite, $petit = false)
    {
        $repositoryMeans = $this->getDoctrine()->getManager()->getRepository(Means::class);
        
        $i = 0;
        
        if(is_string($entite) || is_int($entite))
        {
            $ressource = $repositoryMeans->findOneById($entite);
            
            $texteRessource = ($petit === false) ? "<div class=\"ressources" : "<div class=\"ressources ressource-petite";
            
            if($ressource->getType() === "V")
            {
                $texteRessource .= " ressource-video";
            }

            $texteRessource .= "\">";
            
            if($ressource->getType() === "I")
            {
                $texteRessource .= "<img src=" . "/" . $ressource->getWebPath() . ">";
            }
                
            elseif($ressource->getType() === "S")
            {
                $texteRessource .= "<audio src=" . "/". $ressource->getWebPath() . " controls></audio>";
            }
                
            elseif($ressource->getType() === "V")
            {
                if($petit === false){
                    $texteRessource .= "<iframe width=\"600px\" height=\"450px\" src=\"" . $ressource->getLien() . "\" frameborder=\"0\" allowfullscreen></iframe>";
                }

                else{
                    $texteRessource .= "<iframe width=\"375px\" height=\"280px\" src=\"" . $ressource->getLien() . "\" frameborder=\"0\" allowfullscreen></iframe>";
                }

            }
            
            $texteRessource .= "</div>";
            return $texteRessource;
        }
        
        else{
            foreach($entite->getRessources() as $ressource){
                $i++;
            }
            
            if($i !== 0)
            {
                $texteRessource = ($petit === false) ? "<div class=\"ressources" : "<div class=\"ressources ressource-petite";
                $texteRessource .= "\">";
                
                foreach($entite->getRessources() as $ressource)
                {
                    
                    
                    if($ressource->getType() === "I")
                    {
                        $texteRessource .= "<img src=" . "/" . $ressource->getWebPath() . ">";
                    }

                    elseif($ressource->getType() === "S")
                    {
                        $texteRessource .= "<audio src=" . "/". $ressource->getWebPath() . " controls></audio>";
                    }

                    elseif($ressource->getType() === "V")
                    {
                        if($petit === false){
                            $texteRessource .= "<iframe width=\"600px\" height=\"450px\" src=\"" . $ressource->getLien() . "\" frameborder=\"0\" allowfullscreen></iframe>";
                        }

                        else{
                            $texteRessource .= "<iframe width=\"375px\" height=\"280px\" src=\"" . $ressource->getLien() . "\" frameborder=\"0\" allowfullscreen></iframe>";
                        }
                    }
                }

                $texteRessource .= "</div>";
                return $texteRessource;
            }

            else{
                return null;
            }
        }
    }
    
    public function convertirImageEnRessource()
    {
        $repositoryImage = $this->getDoctrine()->getManager()->getRepository(Image::class);
        $repositoryMeans = $this->getDoctrine()->getManager()->getRepository(Means::class);
        $repositorySubject = $this->getDoctrine()->getManager()->getRepository(Subject::class);
        $repositoryNote = $this->getDoctrine()->getManager()->getRepository(Note::class);
        
        $images = $repositoryImage->findAll();
        
        foreach($images as $image){
            $ressource = new Means();
            
            $ressource->setExtension($image->getExtension());
            $ressource->setType("I");
            $codeAleatoire = hash("sha256", microtime());
            $ressource->setAlt($codeAleatoire);
            $ressource->setMatiere($repositorySubject->findOneByNom("Méthode"));
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($ressource);
            $em->flush();
            
            $ressource = $repositoryMeans->findOneByAlt($codeAleatoire);
            
            
            $imagesNonTrouvees = "Les numéros des images non trouvées sont: ";
            if(file_exists ($image->getUploadRootDir() . "/" .  $image->getId() .'.'. $image->getExtension())){
                rename($image->getUploadRootDir() . "/" .  $image->getId() .'.'. $image->getExtension(), $ressource->getUploadRootDir()  . "/" . $ressource->getId() . $ressource->getExtension() . '.' . $ressource->getExtension());
                $billetTemp = $repositoryNote->findByImage($image->getId());
                foreach($billetTemp as $billet){
                    $billet->setRessource($ressource);
                    $em->persist($billet);
                    $em->flush();
                }
            }
            else{
                $imagesNonTrouvees .= $image->getId() . " ";
            }
            
        }
        
        return $imagesNonTrouvees;
    }
}