<?php

namespace App\Tests\Controller;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\PantherTestCase;

class CommentControllerTest extends PantherTestCase
{
    public function testLoginWithTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/login');

        $client->submitForm("Sign in", [
            "username" => "tester",
            "password" => "tester"
        ]);

        $this->assertSelectorTextContains('h1', 'Hi tester');
    }

    public function testPublishAPostWithTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');

        $client->submitForm("Publish", [
            "post[description]" => "Lorem ipsum dolor sit amet"
        ]);

        $this->assertSelectorTextContains('.alert.alert-success', 'Post successfully published');
    }

    public function testLogoutWithTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/logout');

        $this->assertSelectorTextContains('h1', 'Please sign in');
    }

    public function testLoginWithAnotherTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/login');

        $client->submitForm("Sign in", [
            "username" => "anotherTester",
            "password" => "anotherTester"
        ]);

        $this->assertSelectorTextContains('h1', 'Hi anotherTester');
    }

    public function testCanShowPublishedPost(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/post/1');

        $this->assertSelectorTextContains('p', 'Lorem ipsum dolor sit amet');
    }

    public function testPublishACommentFromAnotherTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/post/1');

        $client->submitForm("Publish", [
            "comment[description]" => "This is a comment"
        ]);

        $this->assertSelectorTextContains('.alert.alert-success', 'Comment successfully published');
    }

    public function testDeleteTheCommentFromAnotherTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/post/1');

        $client->findElement(WebDriverBy::linkText("Delete comment"))->click();

        $this->assertSelectorTextContains('.alert.alert-success', 'Comment successfully deleted');
    }

    public function testDisconnectAnotherTesterUser(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/logout');

        $this->assertSelectorTextContains('h1', 'Please sign in');
    }
}
