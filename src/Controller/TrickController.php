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
     * Show a trick
     *
     * @Route("/trick/{id}", name="trick_show_id", requirements={"id": "\d+"})
     *
     * @param Trick $trick
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Trick $trick)
    {
        return $this->render('trick/trick.html.twig', [
            'trick' => $trick
        ]);
    }

    /**
     * Create or edit a trick
     *
     * @Route("/create-trick", name="create_trick")
     * @Route("/edit-trick/{id}", name="edit_trick", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param ObjectManager $manager
     * @param Trick|null $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createOrEdit(Request $request, ObjectManager $manager, Trick $trick = null)
    {
        $trick = $trick ?? new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * Delete a trick
     *
     * @Route("/delete-trick/{id}", name="delete_trick", requirements={"id": "\d+"})
     *
     * @param ObjectManager $manager
     * @param Trick $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(ObjectManager $manager, Trick $trick)
    {
        $trickName = $trick->getName();
        $manager->remove($trick);
        $manager->flush();

        $this->addFlash(
            "notice",
            "Le trick $trickName a été supprimé"
        );

        return $this->redirectToRoute("home");
    }
}
