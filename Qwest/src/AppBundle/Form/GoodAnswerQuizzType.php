<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class GoodAnswerQuizzType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('corps', TextType::class)
            ->add('nbPoint', IntegerType::class)
            
            ->add('commentaire', TextType::class, array(
                'required' => false
            ))
            
            ->add('envoyer', SubmitType::class)
        ;
        
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA,
            function (\Symfony\Component\Form\FormEvent $event)
            {
                $reponse = $event->getData();
                $form = $event->getForm();
                
                if($reponse->getVerite() === null){
                    $form->add('verite', ChoiceType::class, [
                        'choices'  => [
                            'Bonne réponse' => 1,
                            'Mauvaise réponse' => 0
                        ],
                    ]);
                }
                
                else{
                    if($reponse->getVerite() === true)
                    {
                        $selection = 1;
                    }
                    
                    else{
                        $selection = 0;
                    }
                    
                    $form->add('verite', ChoiceType::class, [
                        'choices'  => [
                            'Bonne réponse' => 1,
                            'Mauvaise réponse' => 0
                        ],
                    ]);
                }
            });
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
