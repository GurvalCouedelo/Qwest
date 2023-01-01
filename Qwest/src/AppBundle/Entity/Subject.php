<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Subject
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Subject
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
     * @Assert\Length(max=255)
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Chapter", mappedBy="matiere")
    * @Assert\Valid
    */
    private $chapitre;
    
    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Means", mappedBy="matiere")
    * @ORM\JoinColumn(nullable=true)
    * @Assert\Valid
    */
    private $ressources;


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
     * @return Subject
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
     * Set ordre
     *
     * @param integer $ordre
     * @return Subject
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
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
     * @param \AppBundle\Entity\Exercise $exercice
     * @return Subject
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
     * Add chapitre
     *
     * @param \AppBundle\Entity\Chapter $chapitre
     *
     * @return Subject
     */
    public function addChapitre(\AppBundle\Entity\Chapter $chapitre)
    {
        $this->chapitre[] = $chapitre;

        return $this;
    }

    /**
     * Remove chapitre
     *
     * @param \AppBundle\Entity\Chapter $chapitre
     */
    public function removeChapitre(\AppBundle\Entity\Chapter $chapitre)
    {
        $this->chapitre->removeElement($chapitre);
    }

    /**
     * Get chapitre
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChapitre()
    {
        return $this->chapitre;
    }

    /**
     * Add ressource
     *
     * @param \AppBundle\Entity\Means $ressource
     *
     * @return Subject
     */
    public function addRessource(\AppBundle\Entity\Means $ressource)
    {
        $this->ressources[] = $ressource;

        return $this;
    }

    /**
     * Remove ressource
     *
     * @param \AppBundle\Entity\Means $ressource
     */
    public function removeRessource(\AppBundle\Entity\Means $ressource)
    {
        $this->ressources->removeElement($ressource);
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
}
