<?php

namespace App\DataFixtures;

use App\Entity\Equipment;
use App\Entity\Location;
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

        // Equipment
        $oop = new Equipment('Overhead-projector');
        $workstation = new Equipment('Workstation');
        $networkConnection = new Equipment('Network connection');
        $phone = new Equipment('Telephone');

        $manager->persist($oop);
        $manager->persist($workstation);
        $manager->persist($networkConnection);
        $manager->persist($phone);

        $mr1 = new Location('Meeting Room 1');
        $mr1->addEquipment($oop);
        $mr1->addEquipment($workstation);
        $mr1->addEquipment($networkConnection);
        $mr1->addEquipment($phone);

        $mr2 = new Location('Meeting Room 2');
        $mr2->addEquipment($workstation);
        $mr2->addEquipment($networkConnection);

        $lib = new Location('Library');
        $lib->addEquipment($workstation);
        $lib->addEquipment($networkConnection);

        $cafe = new Location('Coffee Shop');

        $manager->persist($mr1);
        $manager->persist($mr2);
        $manager->persist($lib);
        $manager->persist($cafe);

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
