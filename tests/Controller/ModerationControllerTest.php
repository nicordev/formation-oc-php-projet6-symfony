<?php

namespace App\Tests\Controller;


use App\Tests\HelperTrait\HelperTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModerationControllerTest extends WebTestCase
{
    use HelperTrait;

    private $client;
    private const COMMENT_EDITED = "Test comment edition from the moderation panel";

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testModerationPanel()
    {
        $crawler = $this->goToModerationPanel();

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains("Modération", $crawler->filter("h1")->text());
        $this->assertEquals(1, $crawler->filter('h1')->count()); // Only 1 h1
    }

    private function goToModerationPanel()
    {
        $this->loginFromHome(true);

        return $this->client->request('GET', '/moderation-panel');
    }
}