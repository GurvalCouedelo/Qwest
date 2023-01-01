<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\GoodAnswerNoSubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use AppBundle\Entity\TypeQuestion;

class VraiOuFauxModificationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('corps', CKEditorType::class)
            ->add("bonneReponses", CollectionType::class, array(
                "entry_type" => GoodAnswerTrueOrFalseType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'entry_options' => array("data_class" => 'AppBundle\Entity\GoodAnswer')
            ))
            ->add('envoyer', SubmitType::class)
        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA,
            function (\Symfony\Component\Form\FormEvent $event)
            {
                $question = $event->getData();
                $form = $event->getForm();

                if($question->getType() === null || $question->getType()->getNom() !== "Ouverte" || $question->getType()->getNom() !== "Texte à trou")
                {
                    $form->add('type', EntityType::class, array(
                        'class'    => TypeQuestion::class,
                        'choice_label' => 'nom',
                        'multiple' => false,
                        "query_builder" => function(\Doctrine\ORM\EntityRepository $er){
                            return $er->createQueryBuilder('tQ')
                                ->where("tQ.nom != :texte")
                                ->setParameter("texte", "Texte à trou")
                                ->andWhere("tQ.nom != :ouverte")
                                ->setParameter("ouverte", "Ouverte")
                                ;
                        }
                    ));
                }
            });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => "AppBundle\Entity\Question"
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