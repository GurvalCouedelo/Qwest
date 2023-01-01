<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class GoodAnswerQuizzWithMeansType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('corps', TextType::class)
            ->add("ressource", new MeansNoSubmitType(), array(
                'required' => false,
            ))
            ->add('nbPoint', TextType::class)
            ->add('verite', ChoiceType::class, array(
                "choices" => array(1 => "Bonne réponse", 0 => "Mauvaise réponse"),
                "expanded" => true,
                "multiple" => false,
                "required" => true,
                'data' => 1
                        
            ))
            ->add('commentaire', TextType::class, array(
                'required' => false
            ))
            
            ->add('envoyer', SubmitType::class)
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