<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;

class CreateUsers extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i <= 10; $i++) {
            [$username, $password] = [
                $faker->userName(),
                $faker->password(8, 20)
            ];

            dump([$username, $password]);

            $manager->persist(
                (new User())
                    ->setUsername($username)
                    ->setPassword($password)
            );
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['users'];
    }
}
