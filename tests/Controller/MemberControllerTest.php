<?php

namespace App\Tests\Controller;


use App\Entity\Member;
use App\Tests\HelperTrait\HelperTrait;
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

    public const TEST_NEW_USER_NAME = "test new member";
    public const TEST_NEW_USER_EMAIL = "new.member@test.com";
    public const TEST_NEW_USER_PASSWORD = "pwdSucks!0";

    public const TEST_USER_NAME = "Jim Nastique";
    public const TEST_USER_EMAIL = "testuser@snow.com";
    public const TEST_USER_HASHED_PASSWORD = '$2y$13$qACYre5/bO7y2jW4n8S.m.Es6vjYpz7x8XBhZxBvckcr.VoC5cvqq';

    public const TEST_USER_NAME_MODIFIED = "test edit member";
    public const TEST_USER_EMAIL_MODIFIED = "test.edit.member@test.com";
    public const TEST_USER_PASSWORD_MODIFIED = "pwdSucks!0mod";

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function tearDown()
    {
        $this->deleteNewTestUser();
        $this->restoreTestUser();
    }

    public function testShowRegistration()
    {
        $crawler = $this->client->request("GET", "/registration");
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains("Inscription", $crawler->filter("h1")->text());

        $form = $crawler->selectButton("Inscription")->form();
        $form['registration[name]'] = self::TEST_NEW_USER_NAME;
        $form['registration[email]'] = self::TEST_NEW_USER_EMAIL;
        $form['registration[password]'] = self::TEST_NEW_USER_PASSWORD;
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

    public function testShowProfile()
    {
        $this->loginFromHome(false);

        // Show the profile of the test member
        $crawler = $this->client->clickLink("Mon profil");
        $this->assertContains(self::TEST_USER_NAME, $crawler->filter("h1")->text());

        // Edit the profile
        $form = $crawler->selectButton("Enregistrer les modifications")->form();
        $form['member[name]'] = self::TEST_USER_NAME_MODIFIED;
        $form['member[email]'] = self::TEST_USER_EMAIL_MODIFIED;
        $this->client->submit($form);

        $crawler = $this->client->followRedirect();
        $this->assertContains(self::TEST_USER_NAME_MODIFIED, $crawler->filter("h1")->text());
        $this->assertContains(self::TEST_USER_EMAIL_MODIFIED, $crawler->filter("#member_email")->attr("value"));
    }

    public function testDeleteMember()
    {
        $this->loginFromHome(false);

        // Show the profile of the test member
        $crawler = $this->client->clickLink("Mon profil");
        $this->assertContains(self::TEST_USER_NAME, $crawler->filter("h1")->text());

        // Delete the member
        $this->client->clickLink("Supprimer le compte");
        $crawler = $this->client->followRedirect();
        $this->assertContains("Votre compte a bien été supprimé", $crawler->filter(".flash-message")->text());
    }

    // Private

    private function deleteNewTestUser()
    {
        $testUser = $this->getMemberByName(self::TEST_NEW_USER_NAME);
        if ($testUser) {
            $manager = $this->client->getContainer()->get('doctrine')->getManager();
            $manager->remove($testUser);
            $manager->flush();
        }
    }

    private function restoreTestUser()
    {
        $testUser = $this->getMemberByName(self::TEST_USER_NAME);

        if (!$testUser) {
            $testUser = $this->getMemberByName(self::TEST_USER_NAME_MODIFIED);

            if (!$testUser) {
                $testUser = new Member();
                $testUser->setRoles([Member::ROLE_USER]);
            }

            $testUser->setName(self::TEST_USER_NAME)
                ->setEmail(self::TEST_USER_EMAIL)
                ->setPassword(self::TEST_USER_HASHED_PASSWORD);

            $manager = $this->client->getContainer()->get('doctrine')->getManager();
            $manager->persist($testUser);
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