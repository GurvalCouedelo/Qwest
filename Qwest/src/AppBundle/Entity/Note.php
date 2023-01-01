<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Note
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Note
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=225)
     * @Assert\Length(max=255)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="contenu", type="string", length=15000)
     * @Assert\Length(max=15000)
     */
    private $contenu;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateModification", type="datetime")
     */
    private $dateModification;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Means")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $ressource;
    
    /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeNote", inversedBy="note")
     * @Assert\Valid
     */
    private $typeNote;
    
    /**
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\Exercise", mappedBy="intro")
    * @Assert\Valid
    */
    private $exercice;

    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set id
     *
     * @return integer 
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     * @return Note
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string 
     */
    public function getContenu()
    {
        return $this->contenu;
    }
    
    /**
     * Get contenu
     *
     * @return string 
     */
    public function getDebutContenu()
    {
        return substr($this->contenu, 0, 750) . "...";
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Note
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateModification
     *
     * @param \DateTime $dateModification
     * @return Note
     */
    public function setDateModification($dateModification)
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    /**
     * Get dateModification
     *
     * @return \DateTime 
     */
    public function getDateModification()
    {
        return $this->dateModification;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return Note
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set typeNote
     *
     * @param \AppBundle\Entity\TypeNote $typeNote
     * @return Note
     */
    public function setTypeNote(\AppBundle\Entity\TypeNote $typeNote = null)
    {
        $this->typeNote = $typeNote;

        return $this;
    }

    /**
     * Get typeNote
     *
     * @return \AppBundle\Entity\TypeNote 
     */
    public function getTypeNote()
    {
        return $this->typeNote;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Add exercice
     *
     * @param \AppBundle\Entity\Exercice $exercice
     * @return Note
     */
    public function addExercice(\AppBundle\Entity\Exercise $exercice)
    {
        $this->exercice[] = $exercice;

        return $this;
    }

    /**
     * Remove exercice
     *
     * @param \AppBundle\Entity\Exercice $exercice
     */
    public function removeExercice(\AppBundle\Entity\Exercise $exercice)
    {
        $this->exercice->removeElement($exercice);
    }

    /**
     * Get exercice
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExercice()
    {
        return $this->exercice;
    }

    /**
     * Set exercice
     *
     * @param \AppBundle\Entity\Exercise $exercice
     * @return Note
     */
    public function setExercice(\AppBundle\Entity\Exercise $exercice = null)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Set banniere
     *
     * @param \AppBundle\Entity\Means $banniere
     *
     * @return Note
     */
    public function setBanniere(\AppBundle\Entity\Means $banniere = null)
    {
        $this->banniere = $banniere;

        return $this;
    }

    /**
     * Get banniere
     *
     * @return \AppBundle\Entity\Means
     */
    public function getBanniere()
    {
        return $this->banniere;
    }

    /**
     * Set ressource
     *
     * @param \AppBundle\Entity\Means $ressource
     *
     * @return Note
     */
    public function setRessource(\AppBundle\Entity\Means $ressource = null)
    {
        $this->ressource = $ressource;

        return $this;
    }

    /**
     * Get ressource
     *
     * @return \AppBundle\Entity\Means
     */
    public function getRessource()
    {
        return $this->ressource;
    }
}
