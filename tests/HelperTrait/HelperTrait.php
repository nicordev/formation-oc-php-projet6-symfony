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
        $form["email"] = "god@snow.com";
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
}