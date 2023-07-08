<?php

namespace App\Tests\Controller;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\PantherTestCase;

class ConversationControllerTest extends PantherTestCase
{
    public function testLoginPage(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/login');

        $this->assertSelectorTextContains('h1', 'Please sign in');

        $client->submitForm("Sign in", [
            "username" => "tester",
            "password" => "tester"
        ]);

        self::assertSelectorTextContains("h1", "Hi tester!");
    }

    public function testFollowAnotherTesterUser()
    {
        $client = static::createPantherClient();
        $client->request('GET', '/user/@anotherTester/follow');

        self::assertSelectorTextContains(".btn.btn-outline-danger", "Unfollow");
    }

    public function testCreateConversation(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/conversation');

        $client->findElement(WebDriverBy::linkText("Create conversation"))->click();

        self::assertSelectorTextContains('h1', 'Create conversation');
    }

    public function testSelectUsersAndCreateConversation(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/conversation/new');

        self::assertSelectorTextContains('h1', 'Create conversation');
    }
}
