<?php

namespace App\Tests\Controller;


use App\Entity\Trick;
use App\Tests\HelperTrait\HelperTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class TrickControllerTest extends WebTestCase
{
    use HelperTrait;

    /**
     * @var Client
     */
    private $client;

    public const NEW_TEST_TRICK_NAME = "Trick test - name";
    public const EDITED_TEST_TRICK_NAME = "Trick test - name - edited";

    public const TEST_COMMENT = "Test comment";
    public const TEST_COMMENT_EDITED = "Test comment - edited";

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testShow()
    {
        // From home to trick page
        $crawler = $this->navigateToTheFirstTrickPageFromHome();

        // Tests as anonymous
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1')->count());
        $this->assertEquals(1, $crawler->filter("div.trick-description")->count());
        $this->assertEquals(1, $crawler->filter("div.trick-metadata")->count());
        $this->assertEquals(1, $crawler->filter("div#trick-comments")->count());
        $this->assertContains("Connectez-vous", $this->client->getResponse()->getContent());
        $this->assertEquals(0, $crawler->filter("div.commands-wrapper")->count());

        // Log in
        $crawler = $this->client->clickLink("Connectez-vous");
        $this->logIn($crawler);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        // Tests as logged in
        $crawler = $this->client->followRedirect();
        $this->assertEquals(1, $crawler->filter("div.commands-wrapper")->count());
    }

    public function testAddTrick()
    {
        // Anonymous users are redirected to the login page
        $this->client->request('GET', '/add-trick');
        $crawler = $this->client->followRedirect();
        $this->assertContains("Connexion", $crawler->filter("h1")->text());

        // Connected users can access to the trick editor
        $this->logIn($crawler);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();

        // Add a test trick
        $form = $crawler->selectButton("Ajouter le trick")->form();
        $form['trick[name]'] = self::NEW_TEST_TRICK_NAME;
        $form['trick[description]'] = "Trick test - description";
        $form['trick[mainImage]'] = "http://testimage.test";
        $this->client->submit($form);

        // The user is then redirected to the new trick page
        $crawler = $this->client->followRedirect();
        $this->assertContains(self::NEW_TEST_TRICK_NAME, $crawler->filter("h1")->text());
    }

    /**
     * Must follow testAddTrick to have a test trick to edit
     */
    public function testEditTrick()
    {
        // Get the test trick created by testAddTrick()
        $trick = $this->getTestTrick();

        // Anonymous users are redirected to the login page
        $this->client->request('GET', "/edit-trick/{$trick->getId()}");
        $crawler = $this->client->followRedirect();
        $this->assertContains("Connexion", $crawler->filter("h1")->text());

        // Connected users can access to the trick editor
        $this->logIn($crawler);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->followRedirect();

        // Add a test trick
        $form = $crawler->selectButton("Enregistrer les modifications")->form();
        $form['trick[name]'] = self::EDITED_TEST_TRICK_NAME;
        $form['trick[description]'] = "Trick test - description - edited";
        $form['trick[mainImage]'] = "http://testimageedited.test";
        $this->client->submit($form);

        // The user is then redirected to the edited trick page
        $crawler = $this->client->followRedirect();
        $this->assertContains(self::EDITED_TEST_TRICK_NAME, $crawler->filter("h1")->text());
    }

    public function testDeleteTrick()
    {
        // Get the test trick created by testAddTrick() or the version edited by testEditTrick()
        $trick = $this->getTestTrick();

        // Anonymous users are redirected to the login page
        $this->client->request('GET', "/delete-trick/{$trick->getId()}");
        $crawler = $this->client->followRedirect();
        $this->assertContains("Connexion", $crawler->filter("h1")->text());

        // Connected users can delete the trick
        $this->logIn($crawler);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect(); // One redirection to delete
        $crawler = $this->client->followRedirect(); // Another one redirection to the home page

        // The trick should be deleted and the user redirected to the home page
        $this->assertContains("SnowTricks", $crawler->filter("h1")->text());
        $this->assertContains("Le trick {$trick->getName()} a été supprimé", $crawler->filter("div.flash-messages")->text());
    }

    public function testAddComment()
    {
        $crawler = $this->navigateToTheFirstTrickPageFromHome();

        // Anonymous so the comment form should not appear
        $this->assertEquals(0, $crawler->filter("form[name=comment]")->count());
        $trickName = $crawler->filter("h1")->text();

        // Logging in as user
        $crawler = $this->client->clickLink("Connectez-vous");
        $this->logInAsUser($crawler);

        // Now we should be able to add a comment
        $crawler = $this->client->followRedirect();
        $this->assertEquals(1, $crawler->filter("form[name=comment]")->count());
        $form = $crawler->selectButton("Publier")->form();
        $form['comment[content]'] = self::TEST_COMMENT;
        $this->client->submit($form);

        // Now we see our comment on the trick page
        $crawler = $this->client->followRedirect();
        $this->assertContains($trickName, $crawler->filter("h1")->text());
        $this->assertContains(self::TEST_COMMENT, $crawler->filter("div.comment-content")->text());
    }

    /**
     * Should be tested after testAddComment to see the test comment
     */
    public function testEditComment()
    {
        $crawler = $this->navigateToTheFirstTrickPageFromHome();

        // Anonymous, so no edit command button should appear
        $this->assertEquals(0, $crawler->filter("div.comment-tools-wrapper")->count());

        // Logging in as user
        $crawler = $this->client->clickLink("Connectez-vous");
        $this->logInAsUser($crawler);

        // Now we should be able to edit the test comment
        $crawler = $this->client->followRedirect();
        $this->assertGreaterThanOrEqual(1, $crawler->filter("div.comment-tools-wrapper")->count());
        $crawler = $this->client->clickLink("🖉");
        $form = $crawler->selectButton("Modifier")->form();
        $form['comment[content]'] = self::TEST_COMMENT_EDITED;
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $this->assertContains(self::TEST_COMMENT_EDITED, $crawler->filter("div.comment-content")->text());
    }

    /**
     * Should be tested after testAddComment to see the test comment
     */
    public function testDeleteComment()
    {
        $crawler = $this->navigateToTheFirstTrickPageFromHome();

        // Anonymous, so no edit command button should appear
        $this->assertEquals(0, $crawler->filter("div.comment-tools-wrapper")->count());

        // Logging in as user
        $crawler = $this->client->clickLink("Connectez-vous");
        $this->logInAsUser($crawler);
        $crawler = $this->client->followRedirect();

        $commentContent = $this->getTestCommentContent($crawler);

        // Now let's delete the test comment!
        $this->assertGreaterThanOrEqual(1, $crawler->filter("div.comment-tools-wrapper")->count());
        $this->client->clickLink("🗑");
        $crawler = $this->client->followRedirect();
        $this->assertRegExp("/Le commentaire de .+ a été supprimé/", $crawler->filter("div.flash-messages")->text());
        $this->assertNotContains($commentContent, $crawler->filter("div.comment-content")->text());
    }

    // Private

    /**
     * Get the content of the test comment, either the new or the edited one
     *
     * @param Crawler $crawler
     * @return string|null
     */
    private function getTestCommentContent(Crawler $crawler): ?string
    {
        $commentContents = $crawler->filter("div.comment-content");
        foreach ($commentContents as $commentContent) {
            if ($commentContent->textContent === self::TEST_COMMENT_EDITED || $commentContent->textContent === self::TEST_COMMENT) {
                return $commentContent->textContent;
            }
        }

        return null;
    }

    /**
     * Open the first trick showing on the home page
     *
     * @return Crawler
     */
    private function navigateToTheFirstTrickPageFromHome(): Crawler
    {
        $crawler = $this->client->request('GET', '/');
        $link = $crawler->filter('a.card-img-link')->first();

        return $this->client->click($link->link());
    }

    /**
     * Get the test trick to either the new or the edited one
     */
    private function getTestTrick(): ?Trick
    {
        $trick = $this->getTrickByName(self::NEW_TEST_TRICK_NAME);
        if (!$trick) {
            $trick = $this->getTrickByName(self::EDITED_TEST_TRICK_NAME);
        }
        return $trick;
    }

    /**
     * Get a trick from its name
     *
     * @param string $name
     * @return Trick
     */
    private function getTrickByName(string $name): ?Trick
    {
        return $this->client->getContainer()->get('doctrine')->getRepository(Trick::class)->findOneBy([
            'name' => $name
        ]);
    }
}