<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @Route("/trick/{id}", name="trick_show_id", requirements={"id": "\d+"})
     */
    public function show(Trick $trick)
    {
        return $this->render('trick/trick.html.twig', [
            'trick' => $trick
        ]);
    }

    /**
     * @Route("/create-trick", name="create_trick")
     * @Route("/edit-trick/{id}", name="edit_trick", requirements={"id": "\d+"})
     */
    public function createOrEdit(Request $request, ObjectManager $manager, Trick $trick = null)
    {
        if (!$trick) {
            $trick = new Trick();
        }

        $form = $this->createForm(TrickType::class, $trick);

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

    /**
     * @Route("/delete-trick/{id}", name="delete_trick", requirements={"id": "\d+"})
     */
    public function delete(ObjectManager $manager, Trick $trick)
    {
        $trickName = $trick->getName();
        $manager->remove($trick);
        $manager->flush();

        return $this->redirectToRoute("home", ["message" => "Le trick $trickName a été supprimé"]);
    }
}
