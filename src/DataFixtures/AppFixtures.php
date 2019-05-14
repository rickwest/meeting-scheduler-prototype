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
        // Create an admin user
        $user = new User();
        $user
            ->setUsername('admin')
            ->setEmail('admin@test.com')
            ->setIsActive(true)
        ;
        $password = $this->encoder->encodePassword($user, '20E!xI&$Zx');
        $user->setPassword($password);

        $manager->persist($user);

        $user = new User();
        $user
            ->setUsername('pete')
            ->setEmail('P.C.Collingwood@shu.ac.uk')
            ->setIsActive(true)
        ;
        $password = $this->encoder->encodePassword($user, 'pc');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();
    }
}
