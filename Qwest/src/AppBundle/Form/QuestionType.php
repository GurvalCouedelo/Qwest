<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\GoodAnswerType;
use AppBundle\Form\MeansType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use AppBundle\Entity\TypeQuestion;

class QuestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('corps', CKEditorType::class)
            ->add("aide", TextType::class, array(
                'required' => false
            ))
            ->add("points", IntegerType::class, array(
                "required" => false
            ))
            ->add("envoyer", SubmitType::class)
        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA,
            function (\Symfony\Component\Form\FormEvent $event)
            {
                $question = $event->getData();
                $form = $event->getForm();
                $session = new Session();

                if($question->getType() !== null && $session->get("creation") !== true){
                    if($question->getType()->getNom() !== "Ouverte" && $question->getType()->getNom() !== "Texte à trou" && $session->get("creation") !== true)
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
                    
                    else{
                        $form->add('type', EntityType::class, array(
                            'class'    => TypeQuestion::class,
                            'choice_label' => 'nom',
                            'multiple' => false
                        ));
                    }
                }


                elseif($question->getType() === null && $session->get("creation") !== null){
                    $form->add('type', EntityType::class, array(
                        'class'    => TypeQuestion::class,
                        'choice_label' => 'nom',
                        'multiple' => false
                    ));

                    $session->remove("creation");
                }
            });
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'StockHG3\appBundle\Entity\Question'
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
