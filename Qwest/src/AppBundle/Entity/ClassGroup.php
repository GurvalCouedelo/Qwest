<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ClassGroup
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ClassGroupRepository")
 */
class ClassGroup
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User", mappedBy="classeGroupe")
     * @Assert\Valid
     */
    private $eleve;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Classroom", inversedBy="classeGroupe")
    * @Assert\Valid
    */
    private $niveau;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->utilisateur = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nom
     *
     * @param string $nom
     * @return ClassGroup
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
     * Add proffesseur
     *
     * @param \AppBundle\Entity\User $proffesseur
     * @return ClassGroup
     */
    public function addProffesseur(\AppBundle\Entity\User $proffesseur)
    {
        $this->proffesseur[] = $proffesseur;

        return $this;
    }

    /**
     * Remove proffesseur
     *
     * @param \AppBundle\Entity\User $proffesseur
     */
    public function removeProffesseur(\AppBundle\Entity\User $proffesseur)
    {
        $this->proffesseur->removeElement($proffesseur);
    }

    /**
     * Get proffesseur
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProffesseur()
    {
        return $this->proffesseur;
    }

    /**
     * Set proffesseur
     *
     * @param \AppBundle\Entity\User $proffesseur
     * @return ClassGroup
     */
    public function setProffesseur(\AppBundle\Entity\User $proffesseur = null)
    {
        $this->proffesseur = $proffesseur;

        return $this;
    }
    /**
     * Set niveau
     *
     * @param \AppBundle\Entity\Classroom $niveau
     * @return ClassGroup
     */
    public function setNiveau(\AppBundle\Entity\Classroom $niveau = null)
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * Get niveau
     *
     * @return \AppBundle\Entity\Classroom 
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * Add eleve
     *
     * @param \AppBundle\Entity\User $eleve
     * @return ClassGroup
     */
    public function addEleve(\AppBundle\Entity\User $eleve)
    {
        $this->eleve[] = $eleve;

        return $this;
    }

    /**
     * Remove eleve
     *
     * @param \AppBundle\Entity\User $eleve
     */
    public function removeEleve(\AppBundle\Entity\User $eleve)
    {
        $this->eleve->removeElement($eleve);
    }

    /**
     * Get eleve
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEleve()
    {
        return $this->eleve;
    }
}
