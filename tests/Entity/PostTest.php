<?php

namespace App\Tests\Entity;

use Faker\Factory;
use Faker\Generator;
use App\Entity\{Post, User};
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    protected readonly mixed $entityManager;

    /**
     * @var Generator
     */
    protected readonly mixed $faker;

    protected function setUp(): void
    {
        $this->entityManager = self::bootKernel()->getContainer()->get('doctrine')->getManager();
        $this->faker = Factory::create();
    }

    /**
     * @throws ORMException
     */
    public function testEmptyPostIsNotValid(): void
    {
        $user = $this->entityManager->getRepository(User::class)->find(1);

        $post = (new Post())
            ->setAuthor($user)
            ->setDescription('');

        $validator = self::bootKernel()->getContainer()
            ->get('test.service_container')
            ->get('validator');

        $err = $validator->validate($post);

        self::assertCount(1, $err);
    }

    /**
     * @throws ORMException
     */
    public function testPostCanNotExceed255Characters(): void
    {
        $user = $this->entityManager->getRepository(User::class)->find(1);

        $post = (new Post())
            ->setAuthor($user)
            ->setDescription($this->faker->realTextBetween(minNbChars: 255, maxNbChars: 300));

        $validator = self::bootKernel()->getContainer()
            ->get('test.service_container')
            ->get('validator');

        $err = $validator->validate($post);

        self::assertCount(1, $err);
    }

    /**
     * @throws ORMException
     */
    public function testPostIsValid(): void
    {
        $user = $this->entityManager->getRepository(User::class)->find(1);

        $post = (new Post())
            ->setAuthor($user)
            ->setDescription($this->faker->text());

        $validator = self::bootKernel()->getContainer()
            ->get('test.service_container')
            ->get('validator');

        $err = $validator->validate($post);

        self::assertCount(0, $err);
    }
}
