<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Difficulty
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Difficulty
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
     * @var integer
     *
     * @ORM\Column(name="numeroOrdre", type="integer")
     */
    private $numeroOrdre;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Exercise", mappedBy="difficulte")
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
     * @return Difficulty
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
     * Set numeroOrdre
     *
     * @param integer $numeroOrdre
     *
     * @return Difficulty
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
     * Constructor
     */
    public function __construct()
    {
        $this->exercice = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add exercice
     *
     * @param \AppBundle\Entity\Difficulty $exercice
     *
     * @return Difficulty
     */
    public function addExercice(\AppBundle\Entity\Difficulty $exercice)
    {
        $this->exercice[] = $exercice;

        return $this;
    }

    /**
     * Remove exercice
     *
     * @param \AppBundle\Entity\Difficulty $exercice
     */
    public function removeExercice(\AppBundle\Entity\Difficulty $exercice)
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
}
