<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @Route("/trick/{id}", name="trickShow")
     */
    public function show()
    {
        $trick = [
            'id' => 1,
            'name' => 'My awesome trick'
        ];

        return $this->render('trick/trick.html.twig', [
            'trick' => $trick
        ]);
    }
}
