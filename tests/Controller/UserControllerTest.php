<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;

class UserControllerTest extends PantherTestCase
{
    public function testLoginWithTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/logout');

        $client->submitForm("Sign in", [
            'username' => 'tester',
            'password' => 'tester'
        ]);

        self::assertSelectorTextContains("h1", "Hi tester!");
    }

    public function testUnfollowAnotherTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/user/@anotherTester/unfollow');

        self::assertSelectorTextContains(".btn.btn-outline-primary", "Follow");
    }

    public function testFollowAnotherTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/user/@anotherTester/follow');

        self::assertSelectorTextContains(".btn.btn-outline-danger", "Unfollow");
    }

    public function testAvoidFollowToItself(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/user/@tester/follow');

        self::assertSelectorTextContains("h1.break-long-words.exception-message", "You can't follow to yourself");
    }

    public function testAvoidUnfollowToItself(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/user/@tester/unfollow');

        self::assertSelectorTextContains("h1.break-long-words.exception-message", "You can't unfollow to yourself");
    }
}
