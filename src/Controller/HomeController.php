<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Service\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public const TRICKS_PER_PAGE = 10;

    /**
     * Show the home page
     *
     * @Route("/{page}", name="home", requirements={"page": "\d+"})
     *
     * @param ObjectManager $manager
     * @param Paginator $paginator
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(ObjectManager $manager, Paginator $paginator, int $page = 1)
    {
        $trickRepo = $manager->getRepository(Trick::class);

        $paginator->update($page, self::TRICKS_PER_PAGE, $trickRepo->count([]));

        $tricks = $trickRepo->findBy([], ["createdAt" => "DESC"], $paginator->itemsPerPage, $paginator->pagingOffset);

        return $this->render('home/home.html.twig', [
            'tricks' => $tricks,
            'paginator' => $paginator
        ]);
    }

    /**
     * Get a page of tricks from an ajax call
     *
     * @Route("/get-page/{page}", name="home_ajax_get_page", requirements={"page": "\d+"})
     *
     * @param ObjectManager $manager
     * @param Paginator $paginator
     * @param int $page
     * @return false|string
     */
    public function getPage(ObjectManager $manager, Paginator $paginator, ?int $page = null)
    {
        $trickRepo = $manager->getRepository(Trick::class);

        $paginator->update($page ?? 1, self::TRICKS_PER_PAGE, $trickRepo->count([]));

        $tricks = $trickRepo->findBy([], ["createdAt" => "DESC"], $paginator->itemsPerPage, $paginator->pagingOffset);

        return $this->render("home/trickDeck.html.twig", ["tricks" => $tricks]);
    }
}
