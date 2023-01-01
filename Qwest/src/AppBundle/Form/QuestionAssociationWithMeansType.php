<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\GoodAnswerNoSubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class QuestionAssociationWithMeansType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('corps', CKEditor::class)
            ->add("ressources", CollectionType::class, array(
                "type" => new MeansNoSubmitType(),
                'allow_add' => false,
                'allow_delete' => false,
                'options' => array("data_class" => 'AppBundle\Entity\Means'),
                'required' => false,
            ))
            ->add("bonneReponses", CollectionType::class, array(
                "type" => new GoodAnswerNoSubmitType(),
                'allow_add' => false,
                'allow_delete' => false,
                'options' => array("data_class" => 'AppBundle\Entity\GoodAnswer')
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
            'data_class' => "AppBundle\Entity\Question",
            'constraints' => new \Symfony\Component\Validator\Constraints\Valid(),
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'stockhg3_appbundle_question';
    }
}