<?php

namespace App\Controller;

use App\Entity\Trick;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function show(ObjectManager $manager)
    {
        $trickRepo = $manager->getRepository(Trick::class);

        $tricks = $trickRepo->findAll();

        return $this->render('home/home.html.twig', ['tricks' => $tricks]);
    }
}