<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Helper\ControllerHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public $tricksPerPage = 10;

    /**
     * @Route("/", name="home")
     * @Route("/{page}", name="home_paging", requirements={"page": "\d+"})
     */
    public function show(ObjectManager $manager, ?int $page = null)
    {
        $trickRepo = $manager->getRepository(Trick::class);

        if ($page) {
            $tricks = $trickRepo->findBy([], ["createdAt" => "DESC"], $this->tricksPerPage, ControllerHelper::getPagingOffset($page, $this->tricksPerPage));
        } else {
            $tricks = $trickRepo->findAll();
        }

        return $this->render('home/home.html.twig', [
            'tricks' => $tricks
        ]);
    }
}