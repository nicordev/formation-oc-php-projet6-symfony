<?php

namespace App\Tests\HelperTrait;


use Symfony\Component\DomCrawler\Crawler;

trait HelperTrait
{
    /**
     * Echo the response's content
     */
    private function printPage()
    {
        echo $this->client->getResponse()->getContent();
    }

    /**
     * Log in using the login form
     *
     * @param Crawler $crawler from the login page
     * @return Crawler
     */
    private function logIn(Crawler $crawler)
    {
        $form = $crawler->selectButton("Connexion")->form();
        $form["email"] = "admin@snow.com";
        $form["password"] = "pwdSucks!0";

        return $this->client->submit($form);
    }

    /**
     * Log in as a common user using the login form
     *
     * @param Crawler $crawler from the login page
     * @return Crawler
     */
    private function logInAsUser(Crawler $crawler)
    {
        $form = $crawler->selectButton("Connexion")->form();
        $form["email"] = "testuser@snow.com";
        $form["password"] = "pwdSucks!0";

        return $this->client->submit($form);
    }

    private function goHome()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertContains("SnowTricks", $crawler->filter("h1")->text());

        return $crawler;
    }

    private function loginFromHome(bool $asAdmin = false)
    {
        $this->goHome();

        $crawler = $this->client->clickLink("Connexion");
        if (!$asAdmin) {
            $this->logInAsUser($crawler);

        } else {
            $this->logIn($crawler);
        }

        $crawler = $this->client->followRedirect();
        $this->assertContains("SnowTricks", $crawler->filter("h1")->text()); // Home page

        return $crawler;
    }
}