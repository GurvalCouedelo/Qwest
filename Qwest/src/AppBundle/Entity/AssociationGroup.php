<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AssociationGroup
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\AssociationGroupRepository")
 */
class AssociationGroup
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
     * @var integer
     *
     * @ORM\Column(name="points", type="integer", nullable=true)
     */
    private $points;
    
    /**
     * @var string
     *
     * @ORM\Column(name="aide", type="string", length=1024, nullable=true)
     * @Assert\Length(max=1024)
     */
    private $aide;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="string", length=1024)
     * @Assert\Length(max=1024)
     */
    private $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="texteATrou", type="string", length=15000, nullable=true)
     * @Assert\Length(max=15000)
     */
    private $texteATrou;
    
    /**
     * @var string
     *
     * @ORM\Column(name="finTexte", type="string", length=1024, nullable=true)
     * @Assert\Length(max=1024)
     */
    private $finTexte;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="groupe", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $question;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Answer", mappedBy="groupe", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $reponseUtilisateur;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Exercise", inversedBy="groupe")
     */
    private $exercice;
    
    /**
    * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Means", mappedBy="groupe")
    * @ORM\JoinColumn(nullable=true)
    */
    private $ressources;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->question = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set description
     *
     * @param string $description
     * @return AssociationGroup
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add question
     *
     * @param \AppBundle\Entity\Question $question
     * @return AssociationGroup
     */
    public function addQuestion(\AppBundle\Entity\Question $question)
    {
        $this->question[] = $question;

        return $this;
    }

    /**
     * Remove question
     *
     * @param \AppBundle\Entity\Question $question
     */
    public function removeQuestion(\AppBundle\Entity\Question $question)
    {
        $this->question->removeElement($question);
    }

    /**
     * Get question
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set exercice
     *
     * @param \AppBundle\Entity\Exercise $exercice
     * @return AssociationGroup
     */
    public function setExercice(\AppBundle\Entity\Exercise $exercice = null)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Get exercice
     *
     * @return \AppBundle\Entity\Exercise 
     */
    public function getExercice()
    {
        return $this->exercice;
    }

    /**
     * Set points
     *
     * @param integer $points
     * @return AssociationGroup
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Add ressources
     *
     * @param \AppBundle\Entity\Means $ressources
     * @return AssociationGroup
     */
    public function addRessource(\AppBundle\Entity\Means $ressources)
    {
        $this->ressources[] = $ressources;

        return $this;
    }

    /**
     * Remove ressources
     *
     * @param \AppBundle\Entity\Means $ressources
     */
    public function removeRessource(\AppBundle\Entity\Means $ressources)
    {
        $this->ressources->removeElement($ressources);
    }

    /**
     * Get ressources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRessources()
    {
        return $this->ressources;
    }

    /**
     * Add reponseUtilisateur
     *
     * @param \AppBundle\Entity\Question $reponseUtilisateur
     * @return AssociationGroup
     */
    public function addReponseUtilisateur(\AppBundle\Entity\Answer $reponseUtilisateur)
    {
        $this->reponseUtilisateur[] = $reponseUtilisateur;

        return $this;
    }

    /**
     * Remove reponseUtilisateur
     *
     * @param \AppBundle\Entity\Question $reponseUtilisateur
     */
    public function removeReponseUtilisateur(\AppBundle\Entity\Answer $reponseUtilisateur)
    {
        $this->reponseUtilisateur->removeElement($reponseUtilisateur);
    }

    /**
     * Get reponseUtilisateur
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReponseUtilisateur()
    {
        return $this->reponseUtilisateur;
    }

    /**
     * Set aide
     *
     * @param string $aide
     * @return AssociationGroup
     */
    public function setAide($aide)
    {
        $this->aide = $aide;

        return $this;
    }

    /**
     * Get aide
     *
     * @return string 
     */
    public function getAide()
    {
        return $this->aide;
    }

    /**
     * Set texteATrou
     *
     * @param string $texteATrou
     * @return AssociationGroup
     */
    public function setTexteATrou($texteATrou)
    {
        $this->texteATrou = $texteATrou;

        return $this;
    }

    /**
     * Get texteATrou
     *
     * @return string 
     */
    public function getTexteATrou()
    {
        return $this->texteATrou;
    }

    /**
     * Set finTexte
     *
     * @param string $finTexte
     * @return AssociationGroup
     */
    public function setFinTexte($finTexte)
    {
        $this->finTexte = $finTexte;

        return $this;
    }

    /**
     * Get finTexte
     *
     * @return string 
     */
    public function getFinTexte()
    {
        return $this->finTexte;
    }
}
