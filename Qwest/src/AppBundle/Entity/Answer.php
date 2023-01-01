<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Answer
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\AnswerRepository")
 */
class Answer
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
     * @ORM\Column(name="corps", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $corps;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\GoodAnswer", inversedBy="reponseUtilisateurQuizz", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $reponseQuizz;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GoodAnswer", inversedBy="reponseUtilisateurAssoc")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $reponseAssoc;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Question", inversedBy="reponseUtilisateur")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $question;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AssociationGroup", inversedBy="reponseUtilisateur", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    private $groupe;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="reponse")
     * @Assert\Valid
     */
    private $utilisateur;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SubQuestion", inversedBy="reponse", cascade={"persist"})
    * @ORM\JoinColumn(nullable=true)
    * @Assert\Valid
    */
    private $sousQuestion;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;
    
    /**
     * @ORM\Column(name="verite", type="boolean", nullable=true)
     */
    private $verite;
    
    /**
     * @ORM\Column(name="correction", type="boolean", nullable=true)
     */
    private $correction;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reponseQuizz = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set corps
     *
     * @param string $corps
     * @return Answer
     */
    public function setCorps($corps)
    {
        $this->corps = $corps;

        return $this;
    }

    /**
     * Get corps
     *
     * @return string 
     */
    public function getCorps()
    {
        return $this->corps;
    }

    /**
     * Set question
     *
     * @param \AppBundle\Entity\Question $question
     * @return Answer
     */
    public function setQuestion(\AppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \AppBundle\Entity\Question 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set utilisateur
     *
     * @param \AppBundle\Entity\User $utilisateur
     * @return Answer
     */
    public function setUtilisateur(\AppBundle\Entity\User $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Answer
     */
    public function setdateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime 
     */
    public function getdateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set reponseQuizz
     *
     * @param \AppBundle\Entity\GoodAnswer $reponseQuizz
     * @return Answer
     */
    public function setReponseQuizz(\AppBundle\Entity\GoodAnswer $reponseQuizz = null)
    {
        $this->reponseQuizz = $reponseQuizz;

        return $this;
    }

    /**
     * Get reponseQuizz
     *
     * @return \AppBundle\Entity\GoodAnswer 
     */
    public function getReponseQuizz()
    {
        return $this->reponseQuizz;
    }

    /**
     * Add reponseQuizz
     *
     * @param \AppBundle\Entity\GoodAnswer $reponseQuizz
     * @return Answer
     */
    public function addReponseQuizz(\AppBundle\Entity\GoodAnswer $reponseQuizz)
    {
        $this->reponseQuizz[] = $reponseQuizz;

        return $this;
    }

    /**
     * Remove reponseQuizz
     *
     * @param \AppBundle\Entity\GoodAnswer $reponseQuizz
     */
    public function removeReponseQuizz(\AppBundle\Entity\GoodAnswer $reponseQuizz)
    {
        $this->reponseQuizz->removeElement($reponseQuizz);
    }

    /**
     * Set verite
     *
     * @param boolean $verite
     * @return Answer
     */
    public function setVerite($verite)
    {
        $this->verite = $verite;

        return $this;
    }

    /**
     * Get verite
     *
     * @return boolean 
     */
    public function getVerite()
    {
        return $this->verite;
    }

    /**
     * Set groupe
     *
     * @param \AppBundle\Entity\AssociationGroup $groupe
     * @return Answer
     */
    public function setGroupe(\AppBundle\Entity\AssociationGroup $groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \AppBundle\Entity\AssociationGroup 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set reponseAssoc
     *
     * @param \AppBundle\Entity\GoodAnswer $reponseAssoc
     * @return Answer
     */
    public function setReponseAssoc(\AppBundle\Entity\GoodAnswer $reponseAssoc = null)
    {
        $this->reponseAssoc = $reponseAssoc;

        return $this;
    }

    /**
     * Get reponseAssoc
     *
     * @return \AppBundle\Entity\GoodAnswer 
     */
    public function getReponseAssoc()
    {
        return $this->reponseAssoc;
    }
    
    public function gettexteATrou()
    {
        return "AAA";
    }

    /**
     * Set correction
     *
     * @param boolean $correction
     *
     * @return Answer
     */
    public function setCorrection($correction)
    {
        $this->correction = $correction;

        return $this;
    }

    /**
     * Get correction
     *
     * @return boolean
     */
    public function getCorrection()
    {
        return $this->correction;
    }

    /**
     * Add sousQuestion
     *
     * @param \AppBundle\Entity\SubQuestion $sousQuestion
     *
     * @return Answer
     */
    public function addSousQuestion(\AppBundle\Entity\SubQuestion $sousQuestion)
    {
        $this->sousQuestion[] = $sousQuestion;

        return $this;
    }

    /**
     * Remove sousQuestion
     *
     * @param \AppBundle\Entity\SubQuestion $sousQuestion
     */
    public function removeSousQuestion(\AppBundle\Entity\SubQuestion $sousQuestion)
    {
        $this->sousQuestion->removeElement($sousQuestion);
    }

    /**
     * Get sousQuestion
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSousQuestion()
    {
        return $this->sousQuestion;
    }

    /**
     * Set sousQuestion
     *
     * @param \AppBundle\Entity\SubQuestion $sousQuestion
     *
     * @return Answer
     */
    public function setSousQuestion(\AppBundle\Entity\SubQuestion $sousQuestion = null)
    {
        $this->sousQuestion = $sousQuestion;

        return $this;
    }
}
