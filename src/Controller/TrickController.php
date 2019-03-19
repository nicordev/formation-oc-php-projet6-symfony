<?php

namespace App\Controller;

use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @Route("/trick/{id}", name="trick_show_id", requirements={"id": "\d+"})
     * @Route("/trick/{name}", name="trick_show_name")
     */
    public function show(Trick $trick)
    {
        return $this->render('trick/trick.html.twig', [
            'trick' => $trick
        ]);
    }

    /**
     * @Route("/create-trick", name="create-trick")
     */
    public function create()
    {
        $trick = new Trick();

        $form = $this->createFormBuilder($trick)
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => "Nom du trick",
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => "Description du trick",
                    'class' => 'form-control'
                ]
            ])
            ->add('trickGroup')
            ->getForm();

        return $this->render('trick/create.html.twig', [
            'createTrickForm' => $form->createView()
        ]);
    }
}
