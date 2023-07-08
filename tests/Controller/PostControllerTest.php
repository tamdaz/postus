<?php

namespace App\Tests\Controller;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\PantherTestCase;

class PostControllerTest extends PantherTestCase
{
    public function testLoginForPost(): void
    {
        $client = self::createPantherClient();
        // Go to /logout to login, the navigator will be redirected automatically to the login page
        $client->request('GET', '/logout');

        $client->submitForm("Sign in", [
            "username" => 'tester',
            "password" => 'tester'
        ]);

        self::assertSelectorTextContains("h1", "Hi tester!");
    }

    public function testPublishAPost(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/');

        $client->submitForm("Publish", [
            "post[description]" => "Lorem ipsum"
        ]);

        self::assertSelectorTextContains("div", "Lorem ipsum");
        self::assertSelectorTextContains(".alert.alert-success", "Post successfully published");
    }

    public function testShowPublishedPost()
    {
        $client = self::createPantherClient();
        $client->request('GET', '/post/1');

        self::assertSelectorTextContains("p", "Lorem ipsum");
    }

    public function testEditThePost(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/post/1/edit');

        $client->submitForm("Publish", [
            "post[description]" => "Lorem ipsum dolor sit amet"
        ]);

        self::assertSelectorTextContains(".alert.alert-success", "Post successfully updated !");
    }

    public function testDeleteThePost(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/');

        $client
            ->findElement(WebDriverBy::id('post1'))
            ->findElement(WebDriverBy::linkText("Delete"))
            ->click();

        self::assertSelectorTextContains(".alert.alert-success", "Post successfully deleted");
    }

    public function testLogoutForPost(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/logout');

        self::assertSelectorTextContains("h1", "Please sign in");
    }
}
