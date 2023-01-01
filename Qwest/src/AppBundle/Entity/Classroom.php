<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classroom
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ClassroomRepository")
 */
class Classroom
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
     * @ORM\Column(name="nom", type="string", length=100)
     * @Assert\Length(max=100)
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="numeroOrdre", type="integer")
     */
    private $numeroOrdre;
    
    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Chapter", mappedBy="classe")
    * @Assert\Valid
    */
    private $chaptire;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\ClassGroup", mappedBy="niveau")
    * @Assert\Valid
    */
    private $classeGroupe;
    
    /**
    * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Exercise", mappedBy="prioritaire")
    * @Assert\Valid
    */
    private $exercices;
    

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
     * Set niveauId
     *
     * @param integer $niveauId
     * @return Classroom
     */
    public function setNiveauId($niveauId)
    {
        $this->niveauId = $niveauId;

        return $this;
    }

    /**
     * Get niveauId
     *
     * @return integer 
     */
    public function getNiveauId()
    {
        return $this->niveauId;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Classroom
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
     * Constructor
     */
    public function __construct()
    {
        $this->exercice = new \Doctrine\Common\Collections\ArrayCollection();
        $this->utilisateur = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add exercice
     *
     * @param \AppBundle\Entity\Exercise $exercice
     * @return Classroom
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
     * Add utilisateur
     *
     * @param \AppBundle\Entity\User $utilisateur
     * @return Classroom
     */
    public function addUtilisateur(\AppBundle\Entity\User $utilisateur)
    {
        $this->utilisateur[] = $utilisateur;

        return $this;
    }

    /**
     * Remove utilisateur
     *
     * @param \AppBundle\Entity\User $utilisateur
     */
    public function removeUtilisateur(\AppBundle\Entity\User $utilisateur)
    {
        $this->utilisateur->removeElement($utilisateur);
    }

    /**
     * Get utilisateur
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Add classeGroupe
     *
     * @param \AppBundle\Entity\ClassGroup $classeGroupe
     * @return Classroom
     */
    public function addClasseGroupe(\AppBundle\Entity\ClassGroup $classeGroupe)
    {
        $this->classeGroupe[] = $classeGroupe;

        return $this;
    }

    /**
     * Remove classeGroupe
     *
     * @param \AppBundle\Entity\ClassGroup $classeGroupe
     */
    public function removeClasseGroupe(\AppBundle\Entity\ClassGroup $classeGroupe)
    {
        $this->classeGroupe->removeElement($classeGroupe);
    }

    /**
     * Get classeGroupe
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClasseGroupe()
    {
        return $this->classeGroupe;
    }

    /**
     * Set numeroOrdre
     *
     * @param integer $numeroOrdre
     * @return Classroom
     */
    public function setNumeroOrdre($numeroOrdre)
    {
        $this->numeroOrdre = $numeroOrdre;

        return $this;
    }

    /**
     * Get numeroOrdre
     *
     * @return integer 
     */
    public function getNumeroOrdre()
    {
        return $this->numeroOrdre;
    }

    /**
     * Add chaptire
     *
     * @param \AppBundle\Entity\Chapter $chaptire
     *
     * @return Classroom
     */
    public function addChaptire(\AppBundle\Entity\Chapter $chaptire)
    {
        $this->chaptire[] = $chaptire;

        return $this;
    }

    /**
     * Remove chaptire
     *
     * @param \AppBundle\Entity\Chapter $chaptire
     */
    public function removeChaptire(\AppBundle\Entity\Chapter $chaptire)
    {
        $this->chaptire->removeElement($chaptire);
    }

    /**
     * Get chaptire
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChaptire()
    {
        return $this->chaptire;
    }

    /**
     * Get exercices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExercices()
    {
        return $this->exercices;
    }
}
