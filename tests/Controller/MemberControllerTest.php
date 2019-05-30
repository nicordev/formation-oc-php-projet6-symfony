<?php

namespace App\Tests\Controller;


use App\Controller\MemberController;
use App\Entity\Member;
use App\Tests\HelperTrait\HelperTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class MemberControllerTest extends WebTestCase
{
    use HelperTrait;

    /**
     * @var Client
     */
    private $client;

    public const TEST_USER_NAME = "bob test";

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function tearDown()
    {
        $this->deleteTestUser();
    }

    public function testShowRegistration()
    {
        $crawler = $this->client->request("GET", "/registration");
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains("Inscription", $crawler->filter("h1")->text());

        $form = $crawler->selectButton("Inscription")->form();
        $form['registration[name]'] = self::TEST_USER_NAME;
        $form['registration[email]'] = "bob@test.com";
        $form['registration[password]'] = "pwdSucks!0";
        $this->client->submit($form);

        // The newly registered user gets redirected to the login page
        $crawler = $this->client->followRedirect();
        $this->assertContains("Connexion", $crawler->filter("h1")->text()); // Login page

        // Logging in
        $this->logIn($crawler);
        $crawler = $this->client->followRedirect();
        $this->assertContains("SnowTricks", $crawler->filter("h1")->text()); // Home page

        // Now that we are logged in we can't access the registration page anymore
        $this->client->request("GET", "/registration");
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    // Private

    private function deleteTestUser()
    {
        $testUser = $this->getMemberByName(self::TEST_USER_NAME);
        if ($testUser) {
            $manager = $this->client->getContainer()->get('doctrine')->getManager();
            $manager->remove($testUser);
            $manager->flush();
        }
    }

    private function getMemberByName(string $name): ?Member
    {
        return $this->client->getContainer()->get('doctrine')->getRepository(Member::class)->findOneBy([
            'name' => $name
        ]);
    }
}