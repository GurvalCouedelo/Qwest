<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Points
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\PointsRepository")
 */
class Points
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
     * @ORM\Column(name="points", type="integer")
     */
    private $points;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Exercise", inversedBy="resultats")
    * @ORM\JoinColumn(nullable=true)
    * @Assert\Valid
    */
    private $exercice;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Message", inversedBy="points", cascade={"persist"} )
    * @ORM\JoinColumn(nullable=true)
    * @Assert\Valid
    */
    private $reponseOuverte;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="resultats", cascade={"persist"})
    * @Assert\Valid
    */
    private $utilisateur;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return strval($this->points);
    }
    /**
     * Set points
     *
     * @param integer $points
     * @return Points
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Points
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
     * Constructor
     */
    public function __construct()
    {
        $this->exercice = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Points
     */
    public function setExercice(\AppBundle\Entity\Exercise $exercice = null)
    {
        $this->exercice = $exercice;

        return $this;
    }

    /**
     * Set utilisateur
     *
     * @param \AppBundle\Entity\User $utilisateur
     * @return Points
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
     * Set reponseOuverte
     *
     * @param \AppBundle\Entity\Message $reponseOuverte
     * @return Points
     */
    public function setReponseOuverte(\AppBundle\Entity\Message $reponseOuverte = null)
    {
        $this->reponseOuverte = $reponseOuverte;

        return $this;
    }

    /**
     * Get reponseOuverte
     *
     * @return \AppBundle\Entity\Message 
     */
    public function getReponseOuverte()
    {
        return $this->reponseOuverte;
    }
}
