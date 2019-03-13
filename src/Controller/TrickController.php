<?php

namespace App\Controller;

use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @Route("/trick/{slug}", name="trickShow")
     */
    public function show($slug)
    {
        $repo = $this->getDoctrine()->getRepository(Trick::class);
        $trick = null;

        if (is_numeric($slug)) {
            $trick = $repo->find($slug);
        } else {
            $trick = $repo->findByName($slug)[0];
        }

        return $this->render('trick/trick.html.twig', [
            'trick' => $trick
        ]);
    }
}
