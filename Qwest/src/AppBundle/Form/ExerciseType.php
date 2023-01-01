<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\SubjectType;
use AppBundle\Form\ClassroomType;
use AppBundle\Form\NoteTypeNoSubmit;
use AppBundle\Entity\User;
use AppBundle\Entity\Points;
use AppBundle\Entity\Difficulty;
use AppBundle\Entity\Chapter;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ExerciseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('titre')
        ->add('intro', NoteNoSubmitType::class)
        ->add('difficulte', EntityType::class, array(
            'class'    => Difficulty::class,
            'choice_label' => 'nom',
            'multiple' => false
        ))
        ->add('chapitre', EntityType::class, array(
            'class'    => Chapter::class,
            'choice_label' => 'nomComplet',
            'multiple' => false,
            "group_by" => "matiere.nom"
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
            'data_class' => 'AppBundle\Entity\Exercise'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'stockhg3_appbundle_exercise';
    }
}
