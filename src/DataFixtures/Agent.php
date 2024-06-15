<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Agent as AppUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Agent extends Fixture
{
    private $userPasswordHasherInterface;

    public function __construct (UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new AppUser();
        $user->setEmail("meskineyoussef13@gmail.com");
        $user->setPassword(
            $this->userPasswordHasherInterface->hashPassword(
                $user, "123456789"
            )
        );

        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $user->setFirstName("Youssef");
        $user->setLastName("Meskine");
        $user->setUsername("YMESKINE");

        $manager->persist($user);
        $manager->flush();
    }
}
