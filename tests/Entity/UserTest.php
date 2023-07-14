<?php

namespace App\Tests\Entity;

use Exception;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class UserTest extends KernelTestCase
{
    public function testCheckAllFieldsIsEmpty(): void
    {
        $kernel = self::bootKernel();

        $user = (new User())
            ->setUsername('')
            ->setPassword('');

        $error = $kernel->getContainer()
            ->get('test.service_container')
            ->get('validator')->validate($user);

        self::assertCount(2, $error);
    }

    public function testCheckUsernameIsEmpty(): void
    {
        $kernel = self::bootKernel();

        $user = (new User())
            ->setUsername('')
            ->setPassword('Nex0Vitality');

        $error = $kernel->getContainer()
            ->get('test.service_container')
            ->get('validator')->validate($user);

        self::assertCount(1, $error);
    }

    public function testCheckPasswordIsEmpty(): void
    {
        $kernel = self::bootKernel();

        $user = (new User())
            ->setUsername('nexovitality')
            ->setPassword('');

        $error = $kernel->getContainer()
            ->get('test.service_container')
            ->get('validator')->validate($user);

        self::assertCount(1, $error);
    }

    public function testCheckPasswordIsNotValid(): void
    {
        $kernel = self::bootKernel();

        $user = (new User())
            ->setUsername('nexovitality')
            ->setPassword('nexovitality');

        $error = $kernel->getContainer()
            ->get('test.service_container')
            ->get('validator')->validate($user);

        self::assertCount(1, $error);
    }

    public function testCheckPasswordIsValid(): void
    {
        $kernel = self::bootKernel();

        $user = (new User())
            ->setUsername('nexovitality')
            ->setPassword('Nex0Vital1ty');

        $error = $kernel->getContainer()
            ->get('test.service_container')
            ->get('validator')->validate($user);

        self::assertCount(0, $error);
    }

    /**
     * @throws Exception
     */
    public function testCheckUserMustBeUnique(): void
    {
        $kernel = self::bootKernel();

        $entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $user = (new User())
            ->setUsername('uniqueTester')
            ->setPassword('Un1qu3Test3r');

        $this->expectException(UniqueConstraintViolationException::class);

        $validator = $kernel->getContainer()
            ->get('test.service_container')
            ->get('validator');

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
