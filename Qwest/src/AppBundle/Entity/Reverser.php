<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reverser
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Reverser
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
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\Question", inversedBy="reverser")
    * @Assert\Valid
    **/
    private $question1;
    
    /**
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\Question", inversedBy="reverser2")
    * @Assert\Valid
    **/
    private $question2;
    

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
     * Set question1
     *
     * @param \AppBundle\Entity\Question $question1
     * @return Reverser
     */
    public function setQuestion1(\AppBundle\Entity\Question $question1 = null)
    {
        $this->question1 = $question1;

        return $this;
    }

    /**
     * Get question1
     *
     * @return \AppBundle\Entity\Question 
     */
    public function getQuestion1()
    {
        return $this->question1;
    }

    /**
     * Set question2
     *
     * @param \AppBundle\Entity\Question $question2
     * @return Reverser
     */
    public function setQuestion2(\AppBundle\Entity\Question $question2 = null)
    {
        $this->question2 = $question2;

        return $this;
    }

    /**
     * Get question2
     *
     * @return \AppBundle\Entity\Question 
     */
    public function getQuestion2()
    {
        return $this->question2;
    }
}
