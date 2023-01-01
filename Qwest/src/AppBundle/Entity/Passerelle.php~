<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Passerelle
 *
 * @ORM\Table(name="passerelle")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PasserelleRepository")
 */
class Passerelle
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
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="passerelle")
    * @Assert\Valid
    */
    private $utilisateur;
    
    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Exercise", inversedBy="passerelle")
    * @Assert\Valid
    */
    private $exercice;


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
     * Set token
     *
     * @param string $token
     *
     * @return Passerelle
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set utilisateur
     *
     * @param \AppBundle\Entity\User $utilisateur
     *
     * @return Passerelle
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
     * Set exercice
     *
     * @param \AppBundle\Entity\Exercise $exercice
     *
     * @return Passerelle
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
}
