<?php

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testShow()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter('h1')->count()); // Only 1 h1

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetPage()
    {
        $this->client->request("GET", "/get-page/2");

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}