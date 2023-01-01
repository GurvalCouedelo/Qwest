<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use AppBundle\Entity\User;

class MessageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $session = new Session();

        $builder
            ->add("objet", TextType::class)
            ->add('contenu', CKEditorType::class)

        ;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::PRE_SET_DATA,
            function (\Symfony\Component\Form\FormEvent $event) use ($session)
            {
                $message = $event->getData();
                $form = $event->getForm();

                if($session->get("permission") === "A"){
                    if($message->getDestinataire() === null)
                    {
                        $form->add('destinataire', EntityType::class, array(
                            'class'    => User::class,
                            'choice_label' => 'nomComplet',
                            'multiple' => false,
                            'expanded' => false,
                            'label' => false,
                            'group_by' => "classeGroupe.nom",
                            "query_builder" => function(\Doctrine\ORM\EntityRepository $er){
                                return $er->createQueryBuilder('u')->where("u.permission = :U")->setParameter("U", "U");
                            }));
                    }
                }

                elseif($session->get("permission") === "U" && $message->getType() !== "R"){
                    if($message->getDestinataire() === null)
                    {
                        $form->add('destinataire', EntityType::class, array(
                            'class'    => User::class,
                            'choice_label' => 'nomComplet',
                            'multiple' => false,
                            'expanded' => false,
                            'label' => false,
                            "query_builder" => function(\Doctrine\ORM\EntityRepository $er){
                                return $er->createQueryBuilder('u')->where("u.permission = :A")->setParameter("A", "A");
                            }));
                    }
                }

                if($this->imbrique === false){
                    $form->add('envoyer', SubmitType::class);
                }
            });
    }

    public function __construct($imbrique = false)
    {
        $this->imbrique = $imbrique;
        
        $session = new Session();
        
        if($session->get("messageArgument")){
            $this->imbrique = $session->get("messageArgument");
            $session->remove("messageArgument");
        }
    }

    public function getSession()
    {
        return $this->session;
    }
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Message'
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