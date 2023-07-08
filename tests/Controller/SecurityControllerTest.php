<?php

namespace App\Tests\Controller;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class SecurityControllerTest extends PantherTestCase
{
    private function clientRequest(string $method, string $route): Crawler
    {
        return $this->createPantherClient()->request($method, $route);
    }

    public function testGetLoginPage(): void
    {
        $this->clientRequest('GET', '/login');

        self::assertSelectorTextContains("h1", "Please sign in");
    }

    public function testGetRegisterPage(): void
    {
        $this->clientRequest('GET', '/register');

        self::assertSelectorTextContains("h1", "Register");
    }

    public function testCreateUserAccount(): void
    {
        $client = $this->createPantherClient();
        $client->request('GET', '/register');

        $client->getWebDriver()->findElement(WebDriverBy::name('registration_form[agreeTerms]'))->click();

        $client->submitForm("Register", [
            'registration_form[username]' => 'newTester',
            'registration_form[password]' => 'newT3st3r'
        ]);

        self::assertSelectorTextContains("h1", "Hi newTester!");
    }

    public function testLogoutUserAccount(): void
    {
        $this->clientRequest('GET', '/logout');

        self::assertSelectorTextContains("h1", "Please sign in");
    }

    public function testLoginWithBadCredentials(): void
    {
        $client = $this->createPantherClient();
        $client->request('GET', '/login');

        $client->submitForm("Sign in", [
            'username' => 'badtester',
            'password' => ''             // empty password
        ]);

        self::assertSelectorTextContains(".alert.alert-danger", "Invalid credentials.");
    }

    public function testRegistrationUniqueUser(): void
    {
        $client = $this->createPantherClient();
        $client->request('GET', '/register');

        $client->getWebDriver()->findElement(WebDriverBy::name('registration_form[agreeTerms]'))->click();

        $client->submitForm("Register", [
            'registration_form[username]' => 'newTester',
            'registration_form[password]' => 'newTester'
        ]);

        self::assertSelectorTextContains(".invalid-feedback", "There is already an account with this username");
    }
}
