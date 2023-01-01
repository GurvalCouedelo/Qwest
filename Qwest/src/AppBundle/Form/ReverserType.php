<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\QuestionRepository;
use AppBundle\Entity\Exercise;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ReverserType extends AbstractType
{
    protected $options;
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $exercice = $this->options;
        $builder
            ->add("question1", EntityType::class, array(
                'class'    => 'StockHG3appBundle:Question',
                'choice_label' => 'corps',
                'multiple' => false,
                "expanded" => true,
                "query_builder" => function(\Doctrine\ORM\EntityRepository $er) use($exercice){
                    return $er->createQueryBuilder('q')->leftJoin("q.exercice", "exercice")->where('exercice.id = :id')->setParameter('id', $exercice);
            }
            ))
            ->add("question2", EntityType::class, array(
                'class'    => 'StockHG3appBundle:Question',
                'choice_label' => 'corps',
                'multiple' => false,
                "expanded" => true,
                "query_builder" => function(\Doctrine\ORM\EntityRepository $er) use($exercice){
                            return $er->createQueryBuilder('q')->leftJoin("q.exercice", "exercice")->where('exercice.id = :id')->setParameter('id', $exercice);
                }
            ))
            ->add("envoyer", SubmitType::class)
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Reverser'
        ));
    }
    
    public function getOptions()
    {
        return $this->options;
    }
    
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'stockhg3_appbundle_reverser';
    }
}
