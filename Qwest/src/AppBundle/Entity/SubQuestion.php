<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * SubQuestion
 *
 * @ORM\Table(name="sub_question")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubQuestionRepository")
 */
class SubQuestion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="corps", type="string", length=1024)
     */
    private $corps;

    /**
     * @var int
     *
     * @ORM\Column(name="points", type="integer", nullable=true)
     */
    private $points;
    
    /**
     * @var int
     *
     * @ORM\Column(name="positionX", type="integer", nullable=true)
     */
    private $positionX;
    
    /**
     * @var int
     *
     * @ORM\Column(name="positionY", type="integer", nullable=true)
     */
    private $positionY;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Question", inversedBy="subQuestion", cascade={"persist"})
    * @Assert\Valid
    */
    private $question;
    
    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\GoodAnswer", mappedBy="sousQuestion", cascade={"persist", "remove"})
    * @Assert\Valid
    */
    private $bonneReponse;
    
    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Answer", mappedBy="sousQuestion", cascade={"persist", "remove"})
    * @Assert\Valid
    */
    private $reponse;
    
    /**
    * @ORM\Column(name="trouOuListe", type="string", length=1, nullable=true)
    * @Assert\Length(max=1)
    */
    private $trouOuListe;


    /**
     * Get id
     *
     * @return int
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
     * Set corps
     *
     * @param string $corps
     *
     * @return SubQuestion
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
     * Set points
     *
     * @param integer $points
     *
     * @return SubQuestion
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set question
     *
     * @param \AppBundle\Entity\Question $question
     *
     * @return SubQuestion
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
     * Get question
     *
     * @return \AppBundle\Entity\Question
     */
    public function addQuestion(\AppBundle\Entity\Question $question)
    {
        $question->addSousQuestion($this);
        $this->setQuestion($question);
    }
    
    /**
     * Get question
     *
     * @return \AppBundle\Entity\Question
     */
    public function add(\AppBundle\Entity\Question $question)
    {
        $question->addSousQuestion($this);
        $this->setQuestion($question);
    }

    /**
     * Set positionX
     *
     * @param integer $positionX
     *
     * @return SubQuestion
     */
    public function setPositionX($positionX)
    {
        $this->positionX = $positionX;

        return $this;
    }

    /**
     * Get positionX
     *
     * @return integer
     */
    public function getPositionX()
    {
        return $this->positionX;
    }

    /**
     * Set positionY
     *
     * @param integer $positionY
     *
     * @return SubQuestion
     */
    public function setPositionY($positionY)
    {
        $this->positionY = $positionY;

        return $this;
    }

    /**
     * Get positionY
     *
     * @return integer
     */
    public function getPositionY()
    {
        return $this->positionY;
    }

    /**
     * Set reponse
     *
     * @param \AppBundle\Entity\Answer $reponse
     *
     * @return SubQuestion
     */
    public function setReponse(\AppBundle\Entity\Answer $reponse = null)
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Get reponse
     *
     * @return \AppBundle\Entity\Answer
     */
    public function getReponse()
    {
        return $this->reponse;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reponse = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reponse
     *
     * @param \AppBundle\Entity\Answer $reponse
     *
     * @return SubQuestion
     */
    public function addReponse(\AppBundle\Entity\Answer $reponse)
    {
        $this->reponse[] = $reponse;

        return $this;
    }

    /**
     * Remove reponse
     *
     * @param \AppBundle\Entity\Answer $reponse
     */
    public function removeReponse(\AppBundle\Entity\Answer $reponse)
    {
        $this->reponse->removeElement($reponse);
    }

    /**
     * Set aide
     *
     * @param string $aide
     *
     * @return SubQuestion
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
     * Add bonneReponse
     *
     * @param \AppBundle\Entity\GoodAnswer $bonneReponse
     *
     * @return SubQuestion
     */
    public function addBonneReponse(\AppBundle\Entity\GoodAnswer $bonneReponse)
    {
        $this->bonneReponse[] = $bonneReponse;

        return $this;
    }

    /**
     * Remove bonneReponse
     *
     * @param \AppBundle\Entity\GoodAnswer $bonneReponse
     */
    public function removeBonneReponse(\AppBundle\Entity\GoodAnswer $bonneReponse)
    {
        $this->bonneReponse->removeElement($bonneReponse);
    }

    /**
     * Get bonneReponse
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBonneReponse()
    {
        return $this->bonneReponse;
    }

    /**
     * Set trouOuListe
     *
     * @param string $trouOuListe
     *
     * @return SubQuestion
     */
    public function setTrouOuListe($trouOuListe)
    {
        $this->trouOuListe = $trouOuListe;

        return $this;
    }

    /**
     * Get trouOuListe
     *
     * @return string
     */
    public function getTrouOuListe()
    {
        return $this->trouOuListe;
    }
}
