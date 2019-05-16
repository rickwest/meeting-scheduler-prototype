<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    private $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('important', CheckboxType::class, [
                'required' => false,
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
            'data_class' => Participant::class,
        ]);
    }
}
