<?php

namespace WarehouseBundle\Form\PickQueue;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use WarehouseBundle\Form\Type\HiddenDateTimeType;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;

use WarehouseBundle\Entity\BookingProduct;
use WarehouseBundle\Entity\LocationProduct;
use WarehouseBundle\DTO\Booking\PickQueue\PickQueueDTO;
use WarehouseBundle\DTO\Booking\PickQueue\PickQueueItemDTO;
use WarehouseBundle\DTO\Booking\PickQueue\PickQueueItemLocationDTO;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BookingPickQueueItemLocationDTOType extends AbstractType
{

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /**
     * Constructor
     * 
     * @param Doctrine $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('POST')
            ->add('quantityStaged', IntegerType::class, array(
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'class' => 'location_pick_staged_qty',
                ],
                //'error_bubbling' => true,
                //'error_mapping' => array(),
                'label' => false,
            ))
            ->add('modified', HiddenDateTimeType::class)
        ;

        // Add listeners
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetDataNew'));
        //$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        //$builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
        //$builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    public function onPreSetDataNew(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();
  
        $config = $form->get('quantityStaged')->getConfig();
        $options = $config->getOptions();
        $form->add(
            'quantityStaged',
            get_class($config->getType()->getInnerType()),
            array_replace(
                $options, 
                [
                    'attr' => [
                        'min' => 0,
                        'max' => $data->getQuantity(),
                    ]
                ]
            )
        );
    }

    public function onPostSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Note that the data is not yet hydrated into the entity.
        $locationProduct = $this->em->getRepository('WarehouseBundle:LocationProduct')->findOneById($data->getId());
        //$this->addElements($form, $locationProduct);
        $config = $form->get('modified')->getConfig();
        $options = $config->getOptions();

        $form->add(
            'modified',
            get_class($config->getType()->getInnerType()),
            array_replace(
                $options, 
                [
                    'mapped' => false,
                    'by_reference' => false,
                    'required' => false,
                    'constraints' => array(
                        /*
                        new \WarehouseBundle\Validator\Constraints\NotModified(array(
                            'value'=>$locationProduct,
                            'current'=>$locationProduct,
                            'original'=>$data
                        ))
                        */
                    ),
                    'data'=>$locationProduct->getModified(),
                ]
            )
        );
    }

    public function onPreSetData(FormEvent $event) {
        $data = $event->getData();
        $form = $event->getForm();

        $locationProduct = $this->em->getRepository('WarehouseBundle:LocationProduct')
            ->findOneById($data->getId());

        $config = $form->get('modified')->getConfig();
        $options = $config->getOptions();

        $form->add(
            'modified',
            get_class($config->getType()->getInnerType()),
            array_replace(
                $options, 
                [
                    //'mapped' => false, 
                    'required' => false,
                    'constraints' => array(
                        /*
                        new \WarehouseBundle\Validator\Constraints\NotModified(array(
                            'value'=>$locationProduct,
                            'current'=>$locationProduct,
                            'original'=>$data
                        ))
                        */
                    ),
                    'data'=>$locationProduct->getModified(),
                ]
            )
        );

        $config = $form->get('quantityStaged')->getConfig();
        $options = $config->getOptions();
        $form->add(
            'quantityStaged',
            get_class($config->getType()->getInnerType()),
            array_replace(
                $options, 
                [
                    'required' => true,
                    'constraints' => array(
                        //new Assert\EqualTo($locationProduct->getModified())
                    ),
                    'data'=>$locationProduct->getStaged(),
                ]
            )
        );
        //$form->get('quantityStaged')->setData($locationProduct->getStaged());
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'data_class' => PickQueueItemLocationDTO::class,
            //'error_bubbling' => TRUE,
            //'always_empty' => true,
        ));
    }

    public function getName()
    {
        return "pick_queue_location";
    }
}
