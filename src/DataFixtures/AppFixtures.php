<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createUser('rick', 'west', 'rickwestdev@gmail.com', ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH']));
        $manager->persist($this->createUser('mehmet', 'ozcan', 'M.B.Ozcan@shu.ac.uk', ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH']));

        // Demo organisation users
        $manager->persist($this->createUser('john'));
        $manager->persist($this->createUser('james'));
        $manager->persist($this->createUser('josh'));
        $manager->persist($this->createUser('nick'));
        $manager->persist($this->createUser('brian'));
        $manager->persist($this->createUser('steve'));
        $manager->persist($this->createUser('alex'));
        $manager->persist($this->createUser('matt'));
        $manager->persist($this->createUser('alison'));
        $manager->persist($this->createUser('jessica'));
        $manager->persist($this->createUser('hannah'));

        $manager->flush();
    }

    public function createUser($name, $password = 'pp', $email = null, $roles = ['ROLE_DEMO'])
    {
        $user = new User();
        $user
            ->setUsername($name)
            ->setEmail($email ? $email : ($name . '@meeting-scheduler.com'))
            ->setIsActive(true)
            ->setRoles($roles)
        ;
        $password = $this->encoder->encodePassword($user, $password);
        $user->setPassword($password);

        return $user;
    }
}
