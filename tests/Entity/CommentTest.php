<?php

namespace App\Tests\Entity;

use Faker\Factory;
use Faker\Generator;
use App\Entity\{Comment, User};
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommentTest extends KernelTestCase
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
    public function testEmptyCommentIsNotValid()
    {
        $user = $this->entityManager->getRepository(User::class)->find(1);
        $post = $user->getPosts()[0];

        $comment = (new Comment())
            ->setPost($post)
            ->setAuthor($user)
            ->setDescription('');

        $validator = self::bootKernel()->getContainer()
            ->get('test.service_container')
            ->get('validator');

        $err = $validator->validate($comment);

        self::assertCount(1, $err);
    }

    /**
     * @throws ORMException
     */
    public function testCommentCanNotExceed255Characters()
    {
        $user = $this->entityManager->getRepository(User::class)->find(1);
        $post = $user->getPosts()[0];

        $comment = (new Comment())
            ->setPost($post)
            ->setAuthor($user)
            ->setDescription($this->faker->realTextBetween(minNbChars: 255, maxNbChars: 300));

        $validator = self::bootKernel()->getContainer()
            ->get('test.service_container')
            ->get('validator');

        $err = $validator->validate($comment);

        self::assertCount(1, $err);
    }

    /**
     * @throws ORMException
     */
    public function testCommentIsValid()
    {
        $user = $this->entityManager->getRepository(User::class)->find(1);
        $post = $user->getPosts()[0];

        $comment = (new Comment())
            ->setPost($post)
            ->setAuthor($user)
            ->setDescription($this->faker->text());

        $validator = self::bootKernel()->getContainer()
            ->get('test.service_container')
            ->get('validator');

        $err = $validator->validate($comment);

        self::assertCount(0, $err);
    }
}
