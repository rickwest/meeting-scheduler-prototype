<?php

namespace App\Form;

use App\Entity\Attendee;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttendeeType extends AbstractType
{
    private $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('required', CheckboxType::class, [
                'required' => false,
                'help' => 'help'
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function($user) {
                    return ucfirst($user->getUsername());
                },
                'placeholder' => 'Please select a member of staff...',
                'choices' => $this->users->findAllDemoUsers(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Attendee::class,
        ]);
    }
}
