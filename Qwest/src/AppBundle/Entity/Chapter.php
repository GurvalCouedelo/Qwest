<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Chapter
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ChapterRepository")
 */
class Chapter
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
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Subject", inversedBy="chapitre")
     * @Assert\Valid
     */
    private $matiere;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Classroom", inversedBy="chapitre")
    * @Assert\Valid
    */
    private $classe;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Exercise", mappedBy="chapitre", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
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
     * Set nom
     *
     * @param string $nom
     *
     * @return Chapter
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }
    
    /**
     * Get nom
     *
     * @return string
     */
    public function getNomComplet()
    {
        return $this->nom . " | " . $this->getClasse()->getNom();
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->exercice = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set matiere
     *
     * @param \AppBundle\Entity\Subject $matiere
     *
     * @return Chapter
     */
    public function setMatiere(\AppBundle\Entity\Subject $matiere = null)
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return \AppBundle\Entity\Subject
     */
    public function getMatiere()
    {
        return $this->matiere;
    }

    /**
     * Add exercice
     *
     * @param \AppBundle\Entity\Exercise $exercice
     *
     * @return Chapter
     */
    public function addExercice(\AppBundle\Entity\Exercise $exercice)
    {
        $this->exercice[] = $exercice;

        return $this;
    }

    /**
     * Remove exercice
     *
     * @param \AppBundle\Entity\Exercise $exercice
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
     * Set classe
     *
     * @param \AppBundle\Entity\Classroom $classe
     *
     * @return Chapter
     */
    public function setClasse(\AppBundle\Entity\Classroom $classe = null)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return \AppBundle\Entity\Classroom
     */
    public function getClasse()
    {
        return $this->classe;
    }
}
