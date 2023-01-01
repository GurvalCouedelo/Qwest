<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class GoodAnswerTrueOrFalseWithSubmitType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('verite', ChoiceType::class, [
                'choices'  => [
                    'Bonne réponse' => 1,
                    'Mauvaise réponse' => 0
                ],
            ])
            ->add('nbPoint', InetegerType::class)
            ->add('commentaire', TextType::class, array(
                "required" => false  
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
            'data_class' => 'AppBundle\Entity\GoodAnswer'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'stockhg3_appbundle_goodanswer';
    }
}