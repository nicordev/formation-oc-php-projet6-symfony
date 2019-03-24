<?php

namespace App\Controller;

use App\Entity\Trick;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
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
     * @Route("/edit-trick/{id}", name="edit-trick", requirements={"id": "\d+"})
     */
    public function createOrEdit(Request $request, ObjectManager $manager, Trick $trick = null)
    {
        if (!$trick) {
            $trick = new Trick();
        }

        $form = $this->createFormBuilder($trick)
            ->add('name')
            ->add('description')
            ->add('trickGroup')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$trick->getId()) {
                $trick->setCreatedAt(new DateTime);
            } else {
                $trick->setModifiedAt(new DateTime);
            }

            $manager->persist($trick);
            $manager->flush();

            return $this->redirectToRoute("trick_show_id", ['id' => $trick->getId()]);
        }

        return $this->render('trick/trickEditor.html.twig', [
            'trickForm' => $form->createView(),
            'editMode' => $trick->getId() !== null
        ]);
    }
}
