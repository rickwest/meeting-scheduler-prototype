<?php

namespace App\Form;

use App\Entity\ParticipantResponse;
use App\Entity\Equipment;
use App\Entity\Location;
use App\Entity\Slot;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('preferenceSet', EntityType::class, [
                'class' => Slot::class,
                'choice_label' => function($slot) {
                    return $slot->getDate()->format('d/M/y H:i');
                },
                'choices' => $options['slots'],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('exclusionSet', EntityType::class, [
                'class' => Slot::class,
                'choice_label' => function($slot) {
                    return $slot->getDate()->format('d/M/y H:i');
                },
                'choices' => $options['slots'],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('equipment', EntityType::class, [
                'class' => Equipment::class,
                'label' => 'Required Equipment',
                'expanded' => true,
                'choice_label' => 'name',
                'required' => false,
                'multiple' => true,
            ])
        ;
        if ($options['userIsImportant']) {
            $builder
                ->add('preferredLocation', EntityType::class, [
                    'class' => Location::class,
                    'choice_label' => 'name',
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ParticipantResponse::class,
            'slots' => [],
            'userIsImportant' => false,
        ]);
    }
}
