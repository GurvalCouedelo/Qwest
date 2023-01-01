<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Message
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\MessageRepository")
 */
class Message extends Controller
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
     * @ORM\Column(name="objet", type="string", length=255)
     * @Assert\Length(max=255)
     */
    private $objet;

    /**
     * @var string
     *
     * @ORM\Column(name="contenu", type="string", length=4096)
     * @Assert\Length(max=4096)
     */
    private $contenu;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="reception", cascade={"persist"})
    * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
    */
    private $destinataire;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="envoyes", cascade={"persist"})
    * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
    */
    private $envoyeur;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TrainDiscussion", inversedBy="message", cascade={"persist"})
    * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
    */
    private $fil;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Question", inversedBy="reponseOuverte", cascade={"persist"})
    * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
    */
    private $question;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Points", mappedBy="reponseOuverte", cascade={"persist", "remove"})
    * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
    */
    private $points;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="type", type="string", length=1)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lu", type="boolean", length=1)
     */
    private $lu;


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
     * Set titre
     *
     * @param string $titre
     * @return Message
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
     * Set contenu
     *
     * @param string $contenu
     * @return Message
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
    public function getContenuDebut()
    {
        return substr($this->contenu, 0, 100) . "...";
    }

    /**
     * Set destinataire
     *
     * @param \AppBundle\Entity\User $destinataire
     * @return Message
     */
    public function setDestinataire(\AppBundle\Entity\User $destinataire = null)
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * Get destinataire
     *
     * @return \AppBundle\Entity\User 
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * Set envoyeur
     *
     * @param \AppBundle\Entity\User $envoyeur
     * @return Message
     */
    public function setEnvoyeur(\AppBundle\Entity\User $envoyeur = null)
    {
        $this->envoyeur = $envoyeur;

        return $this;
    }

    /**
     * Get envoyeur
     *
     * @return \AppBundle\Entity\User 
     */
    public function getEnvoyeur()
    {
        return $this->envoyeur;
    }

    /**
     * Set objet
     *
     * @param string $objet
     * @return Message
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string 
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Message
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
     * Set type
     *
     * @param string $type
     * @return Message
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set fil
     *
     * @param \AppBundle\Entity\TrainDiscussion $fil
     * @return Message
     */
    public function setFil(\AppBundle\Entity\TrainDiscussion $fil = null)
    {
        $this->fil = $fil;

        return $this;
    }

    /**
     * Get fil
     *
     * @return \AppBundle\Entity\TrainDiscussion 
     */
    public function getFil()
    {
        return $this->fil;
    }

    /**
     * Set lu
     *
     * @param boolean $lu
     * @return Message
     */
    public function setLu($lu)
    {
        $this->lu = $lu;

        return $this;
    }

    /**
     * Get lu
     *
     * @return boolean 
     */
    public function getLu()
    {
        return $this->lu;
    }

    /**
     * Set question
     *
     * @param \AppBundle\Entity\Question $question
     * @return Message
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
     * Set points
     *
     * @param \AppBundle\Entity\Points $points
     * @return Message
     */
    public function setPoints(\AppBundle\Entity\Points $points = null)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return \AppBundle\Entity\Points 
     */
    public function getPoints()
    {
        return $this->points;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->points = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add points
     *
     * @param \AppBundle\Entity\Points $points
     * @return Message
     */
    public function addPoint(\AppBundle\Entity\Points $points)
    {
        $this->points[] = $points;

        return $this;
    }

    /**
     * Remove points
     *
     * @param \AppBundle\Entity\Points $points
     */
    public function removePoint(\AppBundle\Entity\Points $points)
    {
        $this->points->removeElement($points);
    }
}
