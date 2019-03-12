<?php

namespace App\Controller;

use MyRandomStuff\MyRandomStuff;
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
            'name' => 'My awesome trick',
            'creationDate' => '2019-03-12 13:19:52',
            'description' => MyRandomStuff::randomString('zog'),
            'medias' => [
                ['path' => '#'],
                ['path' => '#'],
                ['path' => '#']
            ]
        ];

        return $this->render('trick/trick.html.twig', [
            'trick' => $trick
        ]);
    }
}
