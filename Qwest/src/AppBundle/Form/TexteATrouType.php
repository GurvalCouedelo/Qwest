<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Form\GoodAnswerType;
use AppBundle\Entity\GoodAnswer;

class TexteATrouType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $session = new Session();
        $groupe = $session->get("groupe");
        
        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA,
                function (\Symfony\Component\Form\FormEvent $event) use ($groupe)
                {
                    $answer = $event->getData();
                    $form = $event->getForm();

                    $idQuestion  = $answer->getQuestion()->getId();
                    
                    if($answer->getQuestion()->getTrouOuListe() === "L")
                    {
                        $form->add('reponseAssoc', EntityType::class, array(
                            'class'    => GoodAnswer::class,
                            'choice_label' => 'corps',
                            'multiple' => false,
                            'expanded' => false,
                            'label' => false,
                            "query_builder" => function(\Doctrine\ORM\EntityRepository $er) use($groupe, $idQuestion){
                                return $er->createQueryBuilder('gA')->leftJoin("gA.question", "q")->where('q.groupe = :id')->setParameter('id', $groupe)
                                    ->andWhere("q.id = :question")->setParameter("question", $idQuestion)
                                    ->leftJoin("q.type", "t")->andWhere("t.nom = :nom")->setParameter("nom", "Texte à trou")->orderBy("gA.corps");
                        }));
                    }
                    
                    else{
                        $form->add("corps", TextType::class, array(
                            'constraints' => array(
                                new Length(array('max' => 255)),
                            )
                        ));
                    }
                });
    }
    
//    public function __construct($groupe)
//    {
//        $this->groupe = $groupe;
//    }
    
    public function getGroupe()
    {
        return $this->groupe;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Answer'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'stockhg3_appbundle_answer';
    }
}