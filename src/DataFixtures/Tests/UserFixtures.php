<?php

namespace App\DataFixtures\Tests;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1
            ->setUsername('tester')
            ->setPassword($this->passwordHasher->hashPassword($user1, 'tester'));

        $manager->persist($user1);

        $user2 = new User();
        $user2
            ->setUsername('anotherTester')
            ->setPassword($this->passwordHasher->hashPassword($user2, 'anotherTester'));

        $manager->persist($user2);

        $user3 = new User();
        $user3
            ->setUsername('uniqueTester')
            ->setPassword($this->passwordHasher->hashPassword($user3, 'Un1qu3Test3r'));

        $manager->persist($user3);

        $manager->flush();
    }
}
