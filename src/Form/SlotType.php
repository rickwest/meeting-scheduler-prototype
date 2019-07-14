<?php

namespace App\Form;

use App\Entity\Slot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateTimeType::class, [
                'date_widget' => 'choice',
                'date_format' => 'ddMMMyyyy',
                'view_timezone' => 'Europe/London',
                'time_widget' => 'choice',
                'time_label' => 'Time',
                'date_label' => 'Date',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                    'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                ],
                'years' => ['2019'],
                'hours' => ['8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18'],
                'minutes' => ['00', '15', '30', '45'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Slot::class,
        ]);
    }
}
